<link rel="stylesheet" href="/plugins/addon/idcsmart_file_download/template/admin/css/file_download.css" />
<link rel="stylesheet" href="/plugins/addon/idcsmart_file_download/template/admin/css/common/reset.css" />
                <!-- =======内容区域======= -->
                <div id="content" class="help download" v-cloak>
                    <t-card class="list-card-container">
                        <div class="help_card">
                            <div class="help_tabs flex">
                                <div class="tabs flex">
                                    <div class="tabs_item active" @click="changetabs(1)">上传</div>
                                    <div class="tabs_item" @click="changetabs(2)">移动</div>
                                    <div class="tabs_item deletefiles" @click="changetabs(3)">删除</div>
                                </div>
                                <div class="searchbar com-search">
                                    <t-input v-model="params.keywords" class="search-input" placeholder="请输入你需要搜索的内容"
                                        @change="onEnter" clearable>
                                    </t-input>
                                    <t-icon size="20px" name="search" @click="getfilelist(1)" class="com-search-btn" />
                                </div>
                                <!-- <div class="searchbar">
                                    <t-input placeholder="请输入你需要搜索的内容" @Enter="onEnter" @change="changeinput" clearable>
                                    </t-input>
                                    <t-icon size="20px" name="search" slot="suffixIcon" @click="getfilelist"></t-icon>
                                </div> -->
                            </div>
                            <div class="help_table">
                                <t-table hover row-key='id' :maxHeight="140" :pagination="pagination" :data="list"
                                    @Change="changepages" @select-change="rehandleSelectChange" :columns="columns"
                                    max-Height="600px">
                                    <template #name="slotProps">
                                        <t-tooltip placement="top-left" :content="slotProps.row.name" theme="light">
                                            <div class="filename">
                                                <span @click="downloadFile(slotProps.row)">
                                                    {{slotProps.row.name}}</span>
                                            </div>
                                        </t-tooltip>
                                    </template>
                                    <template #pushorback="slotProps">
                                        <t-switch v-model="slotProps.row.hidden?false:true"
                                            @change="onswitch(slotProps.row,$event)" />
                                    </template>
                                    <template #filesize="slotProps">
                                        <div>
                                            {{slotProps.row.filesize / 1024 / 1024 >= 1
                                            ? (slotProps.row.filesize / 1024 / 1024).toFixed(2) + "M"
                                            : parseInt(slotProps.row.filesize / 1024) + "kb"}}</div>
                                    </template>
                                    <template #createtime="slotProps">
                                        {{ getLocalTime(slotProps.row.create_time)
                                        }}
                                    </template>

                                    <template #op="slotProps">
                                        <t-icon name="edit-1" color="#0052D9" style="margin-right: 10px;"
                                            @click="edit(slotProps.row.id)">
                                        </t-icon>
                                    </template>
                                </t-table>
                            </div>
                            <div class="help_pages"></div>
                        </div>
                    </t-card>
                    <t-card class="menucard">
                        <div class="foldername">文件夹</div>
                        <t-tree :data="menudata" ref="tree" hover @click="todetialfiles">
                            <template #operations="{ node }">
                                <t-input v-if="node.value==nodevalue" class="nodeinput" v-model="node.data.label"
                                    :default-Value='node.data.label'>
                                    <t-icon class="close-circle" name="close-circle" color="#0052D9" slot="suffixIcon"
                                        @click="deletenode(node)">
                                    </t-icon>
                                </t-input>

                                <span class="filenum">{{node.data.file_num}}</span>
                                <t-icon v-if="node.value!==nodevalue" class="iconsolt" name="edit-1" color="#0052D9"
                                    style="margin-right: 5px;" @click="editfolder(node)">
                                </t-icon>
                                <t-icon v-if="node.value==nodevalue" name="save" class="iconsolt" color="#0052D9"
                                    style="margin-right: 5px;" @click="savefolder(node.data.label,node.data.id)">
                                </t-icon>
                                <t-icon v-if="node.value==nodevalue" class="iconsolt" name="close-rectangle"
                                    color="#0052D9" @click="canceledit()">
                                </t-icon>
                                <t-popconfirm :visible="isdelete===node.data.id" content="确认删除吗"
                                    @cancel="()=>{isdelete=''}" @Confirm="deletefolder(node,'confirm')">
                                    <t-icon v-if="node.value!==nodevalue" class="iconsolt" name="delete" color="#0052D9"
                                        @click="deletefolder(node)">
                                    </t-icon>
                                </t-popconfirm>


                            </template>
                        </t-tree>
                        <div v-if="appendfolder" class="addfolder">
                            <t-input class="nodeinput" v-model="newfolder">
                                <t-icon class="close-circle" name="close-circle" color="#0052D9" slot="suffixIcon"
                                    @click="()=>{newfolder=''}">
                                </t-icon>
                            </t-input>
                            <div class="iconsolt2">
                                <t-icon name="save" class="iconsolt" @click="addnewfolder">
                                </t-icon>
                                <t-icon name="close-rectangle" class="iconsolt" @click="()=>{appendfolder=false}">
                                </t-icon>
                            </div>
                        </div>

                        <div class="addclass operations" @click="append">新增分类</div>
                    </t-card>
                    <t-dialog header="上传" placement="center" :visible.sync="visible" @Cancel="onCancel"
                        @EscKeydown="onKeydownEsc" @CloseBtnClick="onClickCloseBtn" @Close="close" width="70%"
                        @Confirm="uploadConfirm" @progress="uploadProgress">
                        <div class="uploadfile">
                            <t-upload action="http://kfc.idcsmart.com//console/v1/upload"
                                :format-response="formatResponse" @Change="changeupload" v-model="files"
                                allowUploadDuplicateFile="false" @progress="uploadProgress" theme="custom" multiple>
                                <t-button theme="outline">上传文件</t-button>
                                <span>{{uploadTip}}</span>
                            </t-upload>
                        </div>

                        <t-table :key="key" row-key="index" :data="uploadfilelist" :columns="columns2" maxHeight="80%"
                            class="tableupload">
                            <template #name="slotProps">
                                <span :title="slotProps.row.name">{{slotProps.row.name}}</span>
                            </template>
                            <template #folder="slotProps">
                                <t-select class="demo-select-base"
                                    v-model="slotProps.row.addon_idcsmart_file_folder_id">
                                    <t-option v-for="(item, index) in menudata" :label="item.name" :key="index"
                                        :value="item.id">
                                        {{ item.name }}
                                    </t-option>
                                </t-select>
                            </template>
                            <template #product="slotProps">
                                <t-select v-model="slotProps.row.product_id" class="demo-select-base"
                                    :disabled="slotProps.row.visible_range!='product'" multiple>
                                    <t-option v-for="(item, index) in product" :value="item.id" :label="item.name"
                                        :key="index">
                                        {{ item.name }}
                                    </t-option>
                                </t-select>
                            </template>
                            <template #range="slotProps">
                                <t-select class="demo-select-base" v-model="slotProps.row.visible_range">
                                    <t-option v-for="(item, index) in visible_range" :label="item.label" :key="index"
                                        :value="item.value">
                                        {{ item.label }}
                                    </t-option>
                                </t-select>
                            </template>
                            <template #op="slotProps">
                                <div>
                                    <t-switch v-model="slotProps.row.hidden"> </t-switch>
                                    <t-icon name="delete" color="#0052D9" @click="deleteupfile(slotProps.row.filename)">
                                    </t-icon>
                                </div>
                            </template>
                            </template>
                        </t-table>
                    </t-dialog>
                    <t-dialog header="编辑" placement="center" :visible.sync="showinfo" :onCancel="onCancel"
                        :onEscKeydown="onKeydownEsc" :onCloseBtnClick="onClickCloseBtn" :onClose="close" max-width="50%"
                        confirm-btn='保存' @Confirm="onSubmit">
                        <t-form :data="formData" :rules="rules" ref="form" @reset="onReset">
                            <t-form-item label="文件名称" name="name">
                                <t-input v-model="formData.name"></t-input>
                            </t-form-item>
                            <t-form-item label="所在文件夹" name="folder">
                                <t-select class="demo-select-base" v-model="formData.addon_idcsmart_file_folder_id">
                                    <t-option v-for="(item, index) in menudata" :label="item.name" :key="index"
                                        :value="item.id">
                                        {{ item.name }}
                                    </t-option>
                                </t-select>
                            </t-form-item>
                            <t-form-item label="可见范围" name="scope">
                                <t-select class="demo-select-base" v-model="formData.visible_range">
                                    <t-option v-for="(item, index) in visible_range" :label="item.label" :key="index"
                                        :value="item.value">
                                        {{ item.label }}
                                    </t-option>
                                </t-select>
                            </t-form-item>
                            <t-form-item v-if="formData.visible_range==='product'" label="指定产品" name="specified"
                                style="margin-bottom: 20px;">
                                <t-select v-model="formData.product_id" class="demo-select-base" multiple>
                                    <t-option v-for="(item, index) in product" :value="item.id" :label="item.name"
                                        :key="index">
                                        {{ item.name }}
                                    </t-option>
                                </t-select>
                            </t-form-item>
                        </t-form>
                    </t-dialog>
                    <t-dialog theme="warning" header="提示" body="确定要删除吗？" :visible.sync="visible3" @confirm="onConfirm3"
                        :onClose="close3">
                    </t-dialog>
                    <t-dialog header="移动" placement="center" :visible.sync="visible4" max-width="50%"
                        @Confirm="onSubmitmove">
                        <t-form :data="moveData" :rules="rulesmove" ref="moveform" @reset="onReset">
                            <t-form-item label="文件夹" name="addon_idcsmart_file_folder_id">
                                <t-select class="demo-select-base" @change="moveChange"
                                    v-model="moveData.addon_idcsmart_file_folder_id" style="margin-bottom: 20px;">
                                    <t-option v-for="(item, index) in menudata" :label="item.name" :key="index"
                                        :value="item.id">
                                        {{ item.name }}
                                    </t-option>
                                </t-select>
                            </t-form-item>
                        </t-form>
                    </t-dialog>
                </div>
<script src="/plugins/addon/idcsmart_file_download/template/admin/api/file_download.js"></script>
<script src="/plugins/addon/idcsmart_file_download/template/admin/js/file_download.js"></script>