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
            if (count($arr) != 2 || !is_numeric($arr[0]) || !is_numeric($arr[1]) || $arr[0] > $arr[1] || $arr[1] > 65535 || $arr[0] < 0) {
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
    return check_port_change($str);
}

//验证安全组ip规则
function check_security_ip($str = '')
{
    return check_ipsegment($str);
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

function get_security_group_protocol(){
    return [
        'all' => ['name' => lang('all'), 'port' => '1-65535'],
        'all_tcp' => ['name' => lang('all_tcp'), 'port' => '1-65535'],
        'all_udp' => ['name' => lang('all_udp'), 'port' => '1-65535'],
        'tcp' => ['name' => lang('custom_tcp')],
        'udp' => ['name' => lang('custom_udp')],
        'icmp' => ['name' => 'ICMP', 'port' => '1-65535'],
        // 'gre' => ['name' => 'GRE', 'port' => '1-65535'],
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

