<?php

namespace captcha\tp_captcha\logic;

use captcha\tp_captcha\TpCaptcha;
use think\captcha\Captcha;
use think\facade\Cache;

class TpCaptchaLogic
{
    public function baseDescribe()
    {
        $config = (new TpCaptcha())->Config();

        $Captcha = new Captcha(app('config'), app('session'));

        $captchaConfig = [
            'imageW' => $config['captcha_width'],
            'imageH' => $config['captcha_height'],
            'length' => $config['captcha_length'],
            'codeSet' => $config['code_set'] ?? "1234567890",
        ];

        $response = $Captcha->create($captchaConfig);

        $token = md5(microtime() . rand(10000000, 99999999));
        Cache::set("tp_captcha_" . $token, $GLOBALS['captcha'], 1800); # 缓存验证

        $captcha = $GLOBALS['captcha'];

        $base64 = 'data:png;base64,' . base64_encode($response->getData());

        return ['base64' => $base64, 'captcha' => $captcha, 'token' => $token];
    }

    # 获取验证码
    public function describe($is_admin = false)
    {
        $result = $this->baseDescribe();

        $base64 = $result['base64'];

        $captcha = $result['captcha'];

        $token = $result['token'];

        if ($is_admin) {
            $html = "
                <style>
                    /* admin-captcha */
                    #admin-captcha .captcha-title,
                    #admin-captcha .captcha-footer {
                        display: none !important;
                    }
                    #admin-captcha .captcha-main {
                        line-height: 40px;
                        margin-top: 0 !important;
                        display: flex;
                    }
                    #admin-captcha #captcha-img {
                        height: 40px;
                        cursor: pointer;
                    }
                    #admin-captcha #captcha-input {
                        height: 40px;
                        width: 100%;
                        margin-right: 10px;
                        background-color: var(--td-bg-color-specialcomponent);
                        border-color: var(--td-border-level-2-color);
                        border-radius: var(--td-radius-default);
                        border-style: solid;
                        border-width: 1px;
                        padding: 0 10px;
                    }
                    #admin-captcha #captcha-input:hover {
                        border-color: var(--td-brand-color);
                    }
                    #admin-captcha #captcha-input:focus {
                        outline: none;
                        border-color: var(--td-brand-color);
                        box-shadow: 0 0 0 2px var(--td-brand-color-focus);
                    }
                </style>
                
                <div id='admin-captcha'>
                    <div class='captcha-main'>
                        <input id='captcha-input' type=\"text\" placeholder=\"请输入验证码\" />
                        <img id=\"captcha-img\" src=\"" . $base64 . "\" />
                    </div>
                </div>

                <script>
                
                    let timer = setTimeout(() => { doRightNow() }, 500)
                    let tokenTag = '" . $token . "'
                    let inputTimer = null;

                    // 开始时触发的事件 给各个按钮绑定点击事件
                    function doRightNow(){
                        clearInterval(timer);
                        document.getElementById(\"captcha-img\").onclick = function () { getCaptcha() }
                        $('#captcha-input').bind('input',function(e){ captchaInput(e) })
                    }

                    // 刷新验证码
                    function getCaptcha(){       
                        $.ajax({
                            url:'/captcha/tp_captcha/index/refresh',
                            success:function(result){
                                $('#captcha-img').prop('src',result.base64)
                                // captchaTag = result.captcha
                                tokenTag = result.token
                                captchaCheckSuccsss(false)
                            }
                        })
                    }

                    // 验证码输入框 input事件
                    function captchaInput(e){
                        if(inputTimer){
                            clearTimeout(inputTimer)
                        }
                        inputTimer = setTimeout(function(){
                            // 获取输入的信息
                            const inputValue = $('#captcha-input').val().replace(/\s/g,'')
                            // 调用验证验证码接口 进行后台验证
                            $.ajax({
                                url:'/captcha/tp_captcha/index/verify',
                                method:'post',
                                data:{
                                    'captcha':inputValue,
                                    'token':tokenTag
                                },
                                success:function(result){
                                    if(result.status === 200){
                                        captchaCheckSuccsss(true,inputValue,tokenTag)
                                    }else{
                                        captchaCheckSuccsss(false)
                                    }
                                },
                                error:function(error){
                                    captchaCheckSuccsss(false)
                                }
                            })
                        },500)
                    }
                </script>
            ";
        } else {
            $html = "
            <style>
                .captcha-content{
                    width:5.66rem;
                    height:1.96rem;
                    background:#fff;
                    opacity: 1;
                    position: fixed;
                    left: 50%;
                    top: 50%;
                    margin-top: -1.58rem;
                    transform:translateX(-3.6rem);
                    padding:.6rem .8rem;
                    border-radius 5px;
                }
                .captcha-footer{
                    display: flex;
                    flex-direction: row;
                    justify-content:flex-end;
                    margin-top: .4rem;
                }
                #check-btn{
                    width: 1.12rem;
                    height: .46rem;
                    background: #0058FF;
                    color: #fff;
                    font-size: .16rem;
                    border:none;
                    border-radius: .03rem;
                    cursor: pointer;
                }
                #cancel-btn{
                    width: 1.12rem;
                    height: .46rem;
                    margin-left: .12rem;
                    background: #E7E7E7;
                    color: #1E2736;
                    font-size:.16rem;
                    border: none;
                    border-radius: .03rem;
                    cursor: pointer;
                }
                #captcha-input{
                    width: 4.45rem;
                    height: .40rem;
                    margin-right: .1rem;
                }
                #captcha-img{
                    cursor: pointer;
                    height:.46rem;
                    width:1.11rem;
                }
                .captcha-title{
                    font-size: .24rem;
                    line-height: .24rem;
                }
                @media screen and (max-width: 750px) {
                    .captcha-content{
                        height:3.5rem;
                    }
                    .captcha-footer{
                        display:flex;
                        flex-direction:column;
                    }
                    #check-btn,#cancel-btn{
                        width:100%;
                        height:.8rem;
                        font-size:.34rem
                    }
                    #check-btn{}
                    #cancel-btn{
                        margin-left: 0;
                        margin-top:.2rem;
                    }
                    #captcha-img{
                        cursor: pointer;
                        height:.8rem;
                        width:1.93rem;
                    }
                    #captcha-input{
                        width: 100%;
                        height: .8rem;
                        margin-right: .1rem;
                        font-size:.28rem;
                    }
                    .captcha-title{
                        font-size: .36rem;
                        line-height: .36rem;
                    }
                }
            </style>
            <div id='captcha-outer' style='
                background: rgba(123,123,123,0.6);
                position: fixed;
                left: 0px;
                top: 0px;
                height: 100vh;
                width:100vw;
                z-index: 20;
                filter: alpha(opacity=60);
            '>
                <div class='captcha-content' style=''>
                    <div style=\"width: 100%;display: flex;flex-direction: column;\">
                    <div class=\"captcha-title\">图形验证</div>
                    <div class=\"captcha-main\" style=\"display: flex;flex-direction: row;margin-top: .31rem;\">
                        <input id='captcha-input' type=\"text\" placeholder=\"请输入验证码\">
                        <img id=\"captcha-img\" src=\"" . $base64 . "\">
                    </div>
                        <p id=\"captcha-error-text\" style='color: red;font-size:.16rem'><p/>
                    </div>
                    <div class=\"captcha-footer\">
                        <button calss='captcha-ok' id=\"check-btn\">验证</button>
                        <button calss='captcha-no' id=\"cancel-btn\">取消</button>
                        
                    </div>
                </div>
            </div>


        <script>
            let timer = setTimeout(() => { doRightNow() }, 500)
            let tokenTag = '" . $token . "'
            function getCaptcha(){       
                $.ajax({
                    url:'captcha/tp_captcha/index/refresh',
                    success:function(result){
                        $('#captcha-img').prop('src',result.base64)
                        // captchaTag = result.captcha
                        tokenTag = result.token
                    }
                })
            }

            // 开始时触发的事件 给各个按钮绑定点击事件
            function doRightNow(){
                clearInterval(timer);
                document.getElementById(\"check-btn\").onclick = function () { check() };
                document.getElementById(\"cancel-btn\").onclick = function () { cancel() }
                document.getElementById(\"captcha-img\").onclick = function () { getCaptcha() }
            }

            // 前台验证码验证
            function check() {
                // 获取输入框的值 与localStorage中进行比对
                const inputValue = $('#captcha-input').val().replace(/\s/g,'')
                // 调用验证验证码接口 进行后台验证
                $.ajax({
                    url:'captcha/tp_captcha/index/verify',
                    method:'post',
                    data:{
                        'captcha':inputValue,
                        'token':tokenTag
                    },
                    success:function(result){
                        if(result.status === 200){
                            document.getElementById('captcha-error-text').textContent =''
                            $('#captcha-input').val('')
                            captchaCheckSuccsss(true,inputValue,tokenTag)
                        }else{
                            document.getElementById('captcha-error-text').textContent =result.msg
                            captchaCheckSuccsss(false)
                        }
                    },
                    error:function(error){

                    }
                })
            }

            // 取消
            function cancel() {
                document.getElementById('captcha-error-text').textContent =''
                $('#captcha-input').val('')
                captchaCheckCancel()
            }

        </script>


";
        }

        return $html;
    }

    # 验证
    public function verify($param)
    {
        $token = $param['token'] ?? '';

        $captcha = $param['captcha'] ?? '';

        if (empty($token) || empty($captcha)) {
            return ['status' => 400, 'msg' => '验证失败'];
        }

        if (Cache::get('tp_captcha_' . $token) == strtoupper($captcha)) {

            if (isset($param['base']) && $param['base']) { # 基础验证不清缓存
                return ['status' => 200, 'msg' => '验证成功'];
            }

            # 验证通过,删除验证码缓存
            Cache::delete('tp_captcha_' . $token);

            return ['status' => 200];
        }

        return ['status' => 400, 'msg' => '验证失败'];
    }
}
