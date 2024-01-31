<?php

namespace app\common\validate;

use think\Validate;

/**
 * @title 文件上传验证类
 * @description 接口说明:文件上传验证类
 */
class UploadValidate extends Validate
{
    protected $rule = [
        'file'                 => 'require|file|fileExt:png,jpg,jpeg,gif,doc,docx,key,numbers,pages,pdf,ppt,pptx,txt,rtf,vcf,xls,xlsx,zip,md,ofd,xml,ico|fileMime:image/jpeg,image/png,image/gif,image/bmp,application/vnd.ms-word,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/pdf,application/xml,application/vnd.oasis.opendocument.text,application/x-shockwave-flash,application/x-gzip,application/x-bzip2,application/zip,application/x-rar,text/plain,text/rtf,text/rtfd,application/octet-stream,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,text/x-markdown,application/vnd.ofd,image/vnd.microsoft.icon,image/x-icon|fileSize:157286400',
        'image'                => 'require|image|fileExt:png,jpg,jpeg,gif|fileMime:image/jpeg,image/png,image/gif|fileSize:157286400',
        ];
    protected $message = [
        'file.fileMime'        => 'file_mime_error',
        'file.fileSize'        => 'file_less_than_150M',
    ];

    protected $scene = [
        'file'                 => ['file'],
        'image'                => ['image'],
    ];
}