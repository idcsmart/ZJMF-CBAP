<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>登录</title>
  <link rel="icon" href="">
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/tdesign.min.css" />
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/reset.css" />
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/login.css">
  <script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/tdesign.min.js"></script>
  <script>
    Vue.prototype.lang = window.lang
    const url = "/{$template_catalog}/template/{$themes}/"
  </script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/lang.js"></script>
  <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
</head>


<body>
  <div id="login" v-cloak>
    <div class="login-container">
      <div class="title-container">
        <h1 class="title margin-no">{{lang.login}}</h1>
        <h1 class="title">{{website_name + lang.login_text}}</h1>
      </div>
      <t-form ref="form" :data="formData" :rules="rules" label-width="0" class="item-container" @submit="onSubmit">
        <template>
          <t-form-item name="name">
            <t-input v-model="formData.name" size="large" :placeholder="lang.acount">
              <template #prefix-icon>
                <t-icon name="user" />
              </template>
            </t-input>
          </t-form-item>
          <t-form-item name="password">
            <t-input v-model="formData.password" size="large" type="password" clearable key="password" :placeholder="lang.password">
              <template #prefix-icon>
                <t-icon name="lock-on" />
              </template>
            </t-input>
          </t-form-item>
          <!-- <t-form-item name="captcha" v-if="captcha_admin_login==1">
            <t-input v-model="formData.captcha" size="large" :placeholder="lang.captcha">
            </t-input>
            <img :src="captcha" :alt="lang.captcha" class="captcha" @click="getCaptcha">
          </t-form-item> -->
          <div id="admin-captcha"></div>
          <t-form-item class="btn-container">
            <t-button block size="large" type="submit" :loading="loading">{{lang.login}}</t-button>
          </t-form-item>
          <div class="check-container remember-pwd">
            <t-checkbox v-model="check">{{lang.rember_acount}}</t-checkbox>
          </div>
        </template>
      </t-form>
    </div>
    <footer class="copyright">Copyright @ 2021-2022 Zjmf. All Rights Reserved</footer>
  </div>
  <!-- =======公共======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/axios.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/utils/request.js"></script>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/login.js"></script>

</body>

</html>
