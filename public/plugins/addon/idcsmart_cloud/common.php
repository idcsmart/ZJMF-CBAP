<?php
use addon\idcsmart_cloud\model\IdcsmartVpcModel;

function check_port_change($str)
{
    $res = true;
    if (preg_match("/[^\d\-]/", $str)) {
        $res = false;
    } else {
        $pos = strpos($str, '-');
        if ($pos > 0) {
            $arr = explode('-', $str);
            if (count($arr) != 2 || $arr[0] > $arr[1] || $arr[1] > 65535 || $arr[0] < 0) {
                $res = false;
            }
        } else if ($pos === 0) {
            $res = false;
        } else {
            if ($str < 0 || $str > 65535) {
                $res = false;
            }
        }
    }
    return $res;
}

//验证安全组端口规则
function check_security_port($str = '')
{
    $res = true;
    if (preg_match("/[^\d\-,]/", $str)) {
        $res = false;
    } else {
        $arr = explode(',', $str);
        foreach ($arr as $v) {
            $res = check_port_change($v);
            if (!$res) {
                break;
            }
        }
    }
    return $res;
}

//验证安全组ip规则
function check_security_ip($str = '')
{
    $max = 10;
    $res = true;
    if (strpos($str, ',') !== false) {
        $arr = explode(',', $str);
        if (count($arr) > $max) {
            $res = false;
        } else {
            foreach ($arr as $v) {
                $sub_res = check_ipsegment($v);
                if (!$sub_res) {
                    $res = false;
                    break;
                }
            }
        }
    } else {
        $res = check_ipsegment($str);
    }
    return $res;
}

//验证ip或ip段格式 192.168.1.0/24
function check_ipsegment($ip_segment)
{
    if (empty($ip_segment) || preg_match('/[^\d\.\/]/', $ip_segment)) {
        return false;
    }
    if ($ip_segment === '0.0.0.0/0') {
        return true;
    }
    if (strpos($ip_segment, '/') !== false) {
        $arr = explode('/', $ip_segment);
        $ip = $arr[0];
        $prefix = $arr[1];
        $ip_arr = explode('.', $ip);
        if (count($arr) != 2 || !check_ip($ip) || $prefix > 32 || $prefix < 16) {
            return false;
        }
        if ($prefix == 16) {
            return $ip_arr[2] == 0 && $ip_arr[3] == 0;
        } elseif ($prefix > 16 && $prefix < 24) {
            if ($ip_arr[3] != 0) {
                return false;
            }
            $count = pow(2, 24 - $prefix);
            for ($i = 0; $i <= 255; $i += $count) {
                if ($i == $ip_arr[2]) {
                    return true;
                }
            }
        } elseif ($prefix == 24) {
            return $ip_arr[3] == 0;
        } elseif ($prefix <= 32) {
            $count = pow(2, 32 - $prefix);
            for ($i = 0; $i <= 255; $i += $count) {
                if ($i == $ip_arr[3]) {
                    return true;
                }
            }
        }
    } else {
        return check_ip($ip_segment);
    }
    return false;
}

function check_ip($ip)
{
    if ($ip === '0.0.0.0')
        return false;
    return ip2long($ip) ? true : false;
}

function batch_curl($data, $timeout = 30, $request = 'POST')
{
    $queue = curl_multi_init();
    $map = [];
    foreach ($data as $k => $v) {
        $ch = curl_init();
        $v['data'] = $v['data'] ?? [];

        if($request == 'GET'){
            $s = '';
            if(isset($v['data']) && !empty($v['data'])){
                foreach($v['data'] as $key => $value){
                    if(empty($value)){
                        $v['data'][$key] = '';
                    }
                }
                $s = http_build_query($v['data']);
            }
            if($s){
                $s = '?'.$s;
            }
            curl_setopt($ch, CURLOPT_URL, $v['url'].$s);
        }else{
            curl_setopt($ch, CURLOPT_URL, $v['url']);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if($request == 'GET'){
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        }
        if($request == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1);
            if(is_array($v['data'])){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v['data']));
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, $v['data']);
            }
        }
        if($request == 'PUT' || $request == 'DELETE'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
            if(is_array($v['data'])){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v['data']));
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, $v['data']);
            }
        }

        // curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        if(isset($v['header']) && !empty($v['header'])){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $v['header']);
        }
        curl_multi_add_handle($queue, $ch);
        $map[$k] = $ch;
    }
    $active = null;

    // execute the handles
    do {
        $mrc = curl_multi_exec($queue, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active > 0 && $mrc == CURLM_OK) {
        if (curl_multi_select($queue, 1) != -1) {
            do {
                $mrc = curl_multi_exec($queue, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
    $responses = [];
    foreach ($map as $k => $ch) {
        $res = curl_multi_getcontent($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($curl_errno > 0) {
            $responses[$k] = ['http_code'=>$http_code, 'error'=>$error , 'content' => $res];
        } else {
            $responses[$k] = ['http_code'=>$http_code, 'error'=>$error , 'content' => $res];
        }
        curl_multi_remove_handle($queue, $ch);
        curl_close($ch);
    }
    curl_multi_close($queue);
    return $responses;
}

function check_ips($str)
{
    if (strpos($str, '/') === false) {
        return false;
    }
    $arr = explode('/', $str);
    $ip = $arr[0];
    if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
        return false;
    }
    $prefix = $arr[1];
    $ip_arr = explode('.', $ip);
    if ($prefix < 32 && $prefix > 24) {
        $count = pow(2, 32 - $prefix);
        $in_arr = false;
        for ($i = 0; $i <= 255; $i += $count) {
            if ($i == $ip_arr[3]) {
                $in_arr = true;
                break;
            }
        }
        if (!$in_arr) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
    } else if ($prefix == 24) {
        if ($ip_arr[3] != 0) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = $ip_arr[0] . '.' . $ip_arr[1] . '.' . $ip_arr[2] . '.255';
    } else if ($prefix < 24 && $prefix > 16) {
        if ($ip_arr[3] != 0) {
            return false;
        }

        $count = pow(2, 24 - $prefix);
        $in_arr = false;
        for ($i = 0; $i <= 255; $i += $count) {
            if ($i == $ip_arr[2]) {
                $in_arr = true;
                break;
            }
        }
        if (!$in_arr) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
    } else if ($prefix == 16) {
        if ($ip_arr[2] != 0 || $ip_arr[3] != 0) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = $ip_arr[0] . '.' . $ip_arr[1] . '.255.255';
    } else if ($prefix < 16 && $prefix > 8) {
        if ($ip_arr[2] != 0 || $ip_arr[3] != 0) {
            return false;
        }

        $count = pow(2, 16 - $prefix);
        $in_arr = false;
        for ($i = 0; $i <= 255; $i += $count) {
            if ($i == $ip_arr[1]) {
                $in_arr = true;
                break;
            }
        }
        if (!$in_arr) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
    } else if ($prefix == 8) {
        if ($ip_arr[1] != 0 || $ip_arr[2] != 0 || $ip_arr[3] != 0) {
            return false;
        }
        $ipsection = $ip;
        $first_ip = $ip;
        $last_ip = $ip_arr[0] . '.255.255.255';
    } else {
        return false;
    }
    return ['ipsection' => $ipsection, 'first_ip' => $first_ip, 'last_ip' => $last_ip, 'prefix' => $prefix];
}

function check_vpc_ip($str){
    $res = check_ips($str);
    if(!$res){
        return false;
    }
    if($res['prefix'] > 28 || $res['prefix'] < 16){
        return false;
    }
    // 是否属于他们或他们子网 192.168.0.0/16、172.16.0.0/12、10.0.0.0/8
    $range = [
        [
            'start'=>ip2long('192.168.0.0'),
            'end'=>ip2long('192.168.255.255')
        ],
        [
            'start'=>ip2long('172.16.0.0'),
            'end'=>ip2long('172.31.255.255'),
        ],
        [
            'start'=>ip2long('10.0.0.0'),
            'end'=>ip2long('10.255.255.255')
        ]
    ];
    $first_ip = ip2long($res['first_ip']);
    $last_ip = ip2long($res['last_ip']);

    $result = false;
    foreach($range as $v){
        if($first_ip >= $v['start'] && $last_ip <= $v['end']){
            $result = true;
            break;
        }
    }
    return $result;
}

// 获取自动创建vpc网络网段,范围 10.0.0.0/16-10.255.255.0/16 
function get_auto_vpc_ip($dataCenterId, $clientId){
    $IdcsmartVpcModel = new IdcsmartVpcModel();
    $all = $IdcsmartVpcModel
        ->field('DISTINCT ip')
        ->where('module_idcsmart_cloud_data_center_id', $dataCenterId)
        ->where('client_id', $clientId)
        ->whereLike('ip', "10.%/24")
        ->select()
        ->toArray();
    $used = [];
    foreach($all as $v){
        $used[] = ip2long(str_replace('/24', '', $v['ip']));
    }
    $start = ip2long('10.0.0.0');
    $end = ip2long('10.255.255.0');

    $res = '';
    for($i = $start; $i<=$end; $i+=256){
        if(!in_array($i, $used)){
            $res = $i;
            break;
        }
    }

    if(!empty($res)){
        $ip = long2ip($res).'/24';
    }else{
        $ip = '10.0.0.0/24';
    }
    return $ip;
}

function get_security_group_protocol(){
    return [
        'all' => ['name' => lang('all'), 'port' => '1-65535'],
        'all_tcp' => ['name' => lang('all_tcp'), 'port' => '1-65535'],
        'all_udp' => ['name' => lang('all_udp'), 'port' => '1-65535'],
        'tcp' => ['name' => lang('custom_tcp')],
        'udp' => ['name' => lang('custom_udp')],
        'icmp' => ['name' => 'ICMP', 'port' => '1-65535'],
        'gre' => ['name' => 'GRE', 'port' => '1-65535'],
        'ssh' => ['name' => 'SSH (22)', 'port' => '22'],
        'telnet' => ['name' => 'telnet (23)', 'port' => '23'],
        'http' => ['name' => 'HTTP (80)', 'port' => '80'],
        'https' => ['name' => 'HTTPS (443)', 'port' => '443'],
        'mssql' => ['name' => 'MS SQL(1433)', 'port' => '1433'],
        'oracle' => ['name' => 'Oracle (1521)', 'port' => '1521'],
        'mysql' => ['name' => 'MySQL (3306)', 'port' => '3306'],
        'rdp' => ['name' => 'RDP (3389)', 'port' => '3389'],
        'postgresql' => ['name' => 'PostgreSQL (5432)', 'port' => '5432'],
        'redis' => ['name' => 'Redis (6379)', 'port' => '6379'],
    ];
}

/**
 * 作者: huanghao
 * 时间: 2019-03-26
 * 端口范围去重,合并
 * @param string $str [description]
 * @return [type]      [description]
 */
function format_port_str($str)
{
    if (strpos($str, ',') === false) {
        return $str;
    }
    $arr = explode(',', $str);
    $res = [];
    foreach ($arr as $k => $v) {
        if (strpos($v, '-') !== false) {
            $t = explode('-', $v);
            $res = array_merge($res, range($t[0], $t[1]));
        } else {
            $res[] = (int)$v;
        }
    }
    $res = array_unique($res);
    asort($res);
    $start = null;
    $result = [];
    $i = 0;
    foreach ($res as $k => $v) {
        if (is_null($start)) {
            $start = $res[$k];
            $end = $start;
            $i++;
            continue;
        }
        if ($end + 1 == $v) {
            $end = $v;
            if ($i + 1 == count($res)) {
                $result[] = $start . '-' . $end;
                break;
            }
            $i++;
            continue;
        } else {
            if ($start != $end) {
                $result[] = $start . '-' . $end;
            } else {
                $result[] = $end;
            }
            if ($i + 1 == count($res)) {
                $result[] = $v;
                break;
            }
            $start = $v;
            $end = $v;
        }
        $i++;
    }
    return implode(',', $result);
}