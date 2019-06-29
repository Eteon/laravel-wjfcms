@extends('layouts.admin')
@section('control_name', $control_name)
@section('content')
    <div class="layui-form" lay-filter="layuiadmin-app-list" id="layuiadmin-app-form-list"
         style="padding: 20px 30px 0 0;">
        <div class="layui-form-item">
            <label class="layui-form-label">标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" lay-verify="required" placeholder="请输入标题" autocomplete="off"
                       class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">分类</label>
            <div class="layui-input-block">
                <select name="category_id">
                    <option value="">全部分类</option>
                    @foreach($category_list as $ck=>$cv)
                        @if($category_id == $cv['id'])
                            <option value="{{ $cv['id'] }}" selected>{!! $cv['name'] !!}</option>
                        @else
                            <option value="{{ $cv['id'] }}">{!! $cv['name'] !!}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">作者</label>
            <div class="layui-input-block">
                <input type="text" name="author" placeholder="admin" value="admin" autocomplete="off"
                       class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">标签</label>
            <div class="layui-input-block">
                @foreach($tags_list as $tk=>$tv)
                    <input type="checkbox" name="tags[{{ $tv->name }}]" title="{{ $tv->name }}">
                @endforeach
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">封面图</label>
            <div class="layui-input-block">
                <div class="layui-upload">
                    <div class="layui-upload-list">
                        <input type="hidden" name="cover" value="">
                        <img class="layui-upload-img" id="up_cover" src="/images/config/default-img.jpg" style="cursor:pointer">
                        <p id="up_cover_text"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">描述</label>
            <div class="layui-input-block">
                <textarea name="description" class="layui-textarea"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">内容</label>
            <div class="layui-input-block">
                <textarea id="html" name="html" style="display: none;"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">浏览量</label>
            <div class="layui-input-block">
                <input type="number" name="click" value="99" placeholder="请输入" autocomplete="off"
                       class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否置顶</label>
            <div class="layui-input-block">
                <input type="hidden" name="is_top" value="0">
                <input type="checkbox" lay-filter="is_top" lay-skin="switch"
                       lay-text="是|否">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">状态</label>
            <div class="layui-input-block">
                <input type="hidden" name="status" value="1">
                <input type="checkbox" checked lay-filter="status" lay-skin="switch"
                       lay-text="已审核|待审核">
            </div>
        </div>
        <div class="layui-form-item layui-hide">
            <input type="button" lay-submit lay-filter="layuiadmin-app-form-add" id="layuiadmin-app-form-add"
                   value="确认添加">
            <input type="button" lay-submit lay-filter="layuiadmin-app-form-edit" id="layuiadmin-app-form-edit"
                   value="确认编辑">
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script>
        layui.config({
            base: "/static/layuiadmin/"
        }).extend({
            index: 'lib/index'
        }).use(['index', 'table', 'layedit', 'upload'], function () {
            var $ = layui.$
                , layedit = layui.layedit
                , upload = layui.upload
                , form = layui.form;
            var csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            //图片上传
            var uploadInst = upload.render({
                elem: '#up_cover'
                , url: '/admin/article/uploadImage'
                , headers: {
                    'X-CSRF-TOKEN': csrf_token
                }
                , accept: 'images'
                , field: "file"
                , type: 'images'
                , exts: 'jpg|png|gif' //设置一些后缀，用于演示前端验证和后端的验证
                , before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#up_cover').attr('src', result); //图片链接（base64）
                    });
                }
                , done: function (res) {
                    //如果上传失败
                    if (res.code > 0) {
                        return layer.msg('上传失败');
                    }
                    //上传成功
                    $('input[name="cover"]').val(res.data.src);
                }
                , error: function () {
                    //演示失败状态，并实现重传
                    var up_logo_text = $('#up_cover_text');
                    up_logo_text.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                    up_logo_text.find('.demo-reload').on('click', function () {
                        uploadInst.upload();
                    });
                }
            });

            layedit.set({
                uploadImage: {
                    url: '/admin/article/uploadImage' //接口url
                    , type: 'post'//默认post
                    , data: {
                        _token: csrf_token
                    }
                }
            });
            //注意：layedit.set 一定要放在 build 前面，否则配置全局接口将无效。
            var layedit_index = layedit.build('html'); //建立编辑器

            //监听指定开关
            form.on('switch(status)', function () {
                if (this.checked) {
                    $("input[name='status']").val('1');
                } else {
                    $("input[name='status']").val('0');
                }
            });
            //监听指定开关
            form.on('switch(is_top)', function () {
                if (this.checked) {
                    $("input[name='is_top']").val('1');
                } else {
                    $("input[name='is_top']").val('0');
                }
            });

            $('#layuiadmin-app-form-add').click(function () {
                $("textarea[name='html']").val(layedit.getContent(layedit_index));
            });
        });

        /**
         * 获取url上的参数
         * @param variable
         * @returns {*}
         */
        function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
            return ('');
        }
    </script>
@endsection