<!DOCTYPE html>
<html lang="en" theme-color="default" theme-mode>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>登录状态</title>
  <link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/element.css">
  <style>
    [v-cloak] {
      display: none !important;
    }
  </style>

</head>

<body>
  <div class="template" id="template">
    <div class="userStatus" v-cloak>
      <div class="header-right-item" v-show="!unLogin && isGetData">
        <el-dropdown @command="handleCommand" trigger="click">
          <div class="el-dropdown-header">
            <div class="right-item head-box" id="firstName" v-show="firstName">{{firstName}}</div>
            <i class="right-icon el-icon-arrow-down el-icon--right"></i>
          </div>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item command="account">账户信息</el-dropdown-item>
            <el-dropdown-item command="quit">退出登录</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
      </div>
      <div class="header-right-item" v-show="unLogin && isGetData">
        <div class="un-login" @click="goLogin">
          <img src="/{$template_catalog}/template/{$themes}/img/common/login_icon.png" class="login-icon">登录/注册
        </div>
      </div>
    </div>
  </div>
</body>
<!-- =======公共======= -->
<script src="/{$template_catalog}/template/{$themes}/js/common/vue.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/element.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/axios.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/utils/request.js"></script>

<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/userStatus.js"></script>

</html>

<style>
  .userStatus {
    display: inline-block;
  }

  .un-login {
    cursor: pointer;
    display: flex;
    align-items: center;
    font-size: 0.14rem;
    color: #8692B0;

  }

  .login-icon {
    width: 24px;
    height: 24px;
    margin-right: 0.06rem;
  }

  .el-dropdown-header {
    display: flex;
    flex-direction: row;
    align-items: center;
    cursor: pointer;
  }

  .head-box {
    width: 24px;
    height: 24px;
    font-size: 15px;
    color: #FFFFFF;
    background: #fff;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .right-icon {
    width: 7px;
  }
</style>