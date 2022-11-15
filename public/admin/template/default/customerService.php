{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/customerService.css">
<div id="content" class="transaction table">
    <t-card class="list-card-container" >
        <main>
            <t-form :data="formData"  ref="form"  @submit="onSubmit"  labelWidth="0px" :rules="rules">
                <t-form-item label="在线客服漂浮弹窗源码：" name="content" class="code-box" labelWidth="0px">
                    <t-textarea v-model="formData.content"></t-textarea>
                </t-form-item>
                <t-form-item class="footer">
                    <t-button theme="primary" type="submit">保存</t-button>
                    
                </t-form-item>
            </t-form>
        </main>
    </t-card>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/customerService.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/customerService.js"></script>
{include file="footer"}