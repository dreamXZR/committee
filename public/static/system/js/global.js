/***** HisiPHP By http://www.HisiPHP.com *****/
layui.define(['element', 'form', 'table', 'md5'], function(exports) {
    var $ = layui.jquery,element = layui.element, 
        layer = layui.layer, 
        form = layui.form, 
        table = layui.table,
        md5 = layui.md5;
    var checkBrowser = function() {
        var d = layui.device();
        d.ie && d.ie < 10 && layer.alert("IE" + d.ie + "下体验不佳，推荐使用：Chrome/Firefox/Edge/极速模式");
    }
    checkBrowser();



    /* 导航高亮标记 */
    $('.admin-nav-item').click(function() {
        window.localStorage.setItem("adminNavTag", $(this).attr('href'));
    });
    if (window.localStorage.getItem("adminNavTag")) {
        $('#switchNav a[href="'+window.localStorage.getItem("adminNavTag")+'"]').parent('dd').addClass('layui-this').parents('li').addClass('layui-nav-itemed').siblings('li').removeClass('layui-nav-itemed');
    }
    if (typeof(LAYUI_OFFSET) == 'undefined') {
        layer.config({offset:'60px'});
    } else {
        layer.config({offset:LAYUI_OFFSET});  
    }

    /* 打开/关闭左侧导航 */
    $('#foldSwitch').click(function(){
        var that = $(this);
        if (!that.hasClass('ai-zhankaicaidan')) {
            that.addClass('ai-zhankaicaidan').removeClass('ai-shouqicaidan');
            $('#switchNav').animate({width:'43px'}, 100).addClass('close').hover(function() {
                if (that.hasClass('ai-zhankaicaidan')) {
                    $(this).animate({width:'200px'}, 300);
                    $('#switchNav .fold-mark').removeClass('fold-mark');
                    $('a[href="'+window.localStorage.getItem("adminNavTag")+'"]').parent('dd').addClass('layui-this').parents('li').addClass('layui-nav-itemed').siblings('li').removeClass('layui-nav-itemed');
                }
            },function() {
                if (that.hasClass('ai-zhankaicaidan')) {
                    $(this).animate({width:'43px'}, 300);
                    $('#switchNav .layui-nav-item').addClass('fold-mark').removeClass('layui-nav-itemed');
                }
            });
            $('#switchBody,.footer').animate({left:'43px'}, 100);
            $('#switchNav .layui-nav-item').addClass('fold-mark').removeClass('layui-nav-itemed');
        } else {
            $('a[href="'+window.localStorage.getItem("adminNavTag")+'"]').parent('dd').addClass('layui-this').parents('li').addClass('layui-nav-itemed').siblings('li').removeClass('layui-nav-itemed');
            that.removeClass('ai-zhankaicaidan').addClass('ai-shouqicaidan');
            $('#switchNav').animate({width:'200px'}, 100).removeClass('close');
            $('#switchBody,.footer').animate({left:'200px'}, 100);
            $('#switchNav .fold-mark').removeClass('fold-mark');
        }
    });

    /* 导航菜单切换 */
    $('.main-nav a').click(function () {
        var that = $(this), i = $('.main-nav a').index(this);
        $('.layui-nav-tree').hide().eq(i).show();
    });

    /* 操作提示 */
    $('.help-tips').click(function(){
        layer.tips($(this).attr('data-title'), this, {
            tips: [3, '#009688'],
            time: 5000
        });
        return false;
    });

    /* 全屏切换 */
    $('#fullscreen-btn').click(function(){
        var that = $(this);
        if (!that.hasClass('ai-quanping')) {
            $('#switchBody').css({'z-index':10000});
            $('#switchNav').css({'z-index':900});
            that.addClass('ai-quanping').removeClass('ai-quanping1').parents('.page-body').addClass('fullscreen');
            $('.page-tab-content').css({'min-height':($(window).height()-63)+'px'});
        } else {
            $('#switchBody').css({'z-index':998});
            $('#switchNav').css({'z-index':1000});
            that.addClass('ai-quanping1').removeClass('ai-quanping').parents('.page-body').removeClass('fullscreen');
            $('.page-tab-content').css({'min-height':'auto'});
        }
    });

    /* 静态表格全选 */
    form.on('checkbox(allChoose)', function(data) {
        var child = $(data.elem).parents('table').find('tbody input.checkbox-ids');
        child.each(function(index, item) {
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });



    /* 清空缓存 */
    $('#hisi-clear-cache').on('click', function() {
        var that = $(this);
        layer.open({
            type: 5,
            title: '删除缓存',
            shade: 0.3,
            area: ['205px', '93%'],
            offset: 'rb',
            maxmin: true,
            shadeClose: true,
            closeBtn: false,
            anim: 2,
            content: $('#hisi-clear-cache-tpl').html(),
            success: function(layero, index) {
                form.render('checkbox');
            }
        }); 
        return false;
    });


    /**
     * iframe弹窗
     * @href 弹窗地址
     * @title 弹窗标题
     * @hisi-data {width: '弹窗宽度', height: '弹窗高度', idSync: '是否同步ID', table: '数据表ID(同步ID时必须)', type: '弹窗类型'}
     */
    $(document).on('click', '.j-iframe-pop,.hisi-iframe-pop', function() {
        var that = $(this), query = '';
        var def = {width: '750px', height: '500px', idSync: false, table: 'dataTable', type: 2, url: that.attr('href'), title: that.attr('title')};
        var opt = new Function('return '+ that.attr('hisi-data'))() || {};

        opt.url     = opt.url || def.url;
        opt.title   = opt.title || def.title;
        opt.width   = opt.width || def.width;
        opt.height  = opt.height || def.height;
        opt.type    = opt.type || def.type;
        opt.table   = opt.table || def.table;
        opt.idSync  = opt.idSync || def.idSync;

        if (!opt.url) {
            layer.msg('请设置href参数');
            return false;
        }

        if (opt.idSync) {// ID 同步
            if ($('.checkbox-ids:checked').length <= 0) {
                var checkStatus = table.checkStatus(opt.table);
                if (checkStatus.data.length <= 0) {
                    layer.msg('请选择要操作的数据');
                    return false;
                }

                for (var i in checkStatus.data) {
                    query += '&id[]=' + checkStatus.data[i].id;
                }
            } else {
                $('.checkbox-ids:checked').each(function() {
                    query += '&id[]=' + $(this).val();
                })
            }
        }

        if (opt.url.indexOf('?') >= 0) {
            opt.url += '&hisi_iframe=yes'+query;
        } else {
            opt.url += '?hisi_iframe=yes'+query;
        }

        layer.open({type: opt.type, title: opt.title, content: opt.url, area: [opt.width, opt.height]});
        return false;
    });

    /**
     * 通用状态设置开关
     * @attr data-href 请求地址
     */
    form.on('switch(switchStatus)', function(data) {
        var that = $(this), status = 0;
        if (!that.attr('data-href')) {
            layer.msg('请设置data-href参数');
            return false;
        }
        if (this.checked) {
            status = 1;
        }
        $.get(that.attr('data-href'), {val:status}, function(res) {
            layer.msg(res.msg);
            if (res.code == 0) {
                that.trigger('click');
                form.render('checkbox');
            }
        });
    });

    /**
     * 监听表单提交
     * @attr action 请求地址
     * @attr data-form 表单DOM
     */
    form.on('submit(formSubmit)', function(data) {
        var _form = '',
            that = $(this),
            text = that.text();
        if ($(this).attr('data-form')) {
            _form = $(that.attr('data-form'));
        } else {
            _form = that.parents('form');
        }


        that.prop('disabled', true).text('提交中...');
        $.ajax({
            type: "POST",
            url: _form.attr('action'),
            data: _form.serialize(),
            success: function(res) {
                layer.msg(res.msg);
                if (res.code == 0) {
                    that.prop('disabled', false).text('提交');
                } else {
                    setTimeout(function() {
                        that.text(text).prop('disabled', false);

                        if (res.url != '') {

                            location.href = res.url;
                        } else {
                            location.reload();
                        }

                    }, 1000);
                }
            }
        });
        return false;
    });

    /**
     * 通用TR数据行删除
     * @attr href或data-href 请求地址
     * @attr refresh 操作完成后是否自动刷新
     */
    $(document).on('click', '.j-tr-del,.hisi-tr-del', function() {
        var that = $(this),
            href = !that.attr('data-href') ? that.attr('href') : that.attr('data-href');
        layer.confirm('删除之后无法恢复，您确定要删除吗？', {title:false, closeBtn:0}, function(index){
            if (!href) {
                layer.msg('请设置data-href参数');
                return false;
            }
            $.get(href, function(res) {
                if (res.code == 0) {
                    layer.msg(res.msg);
                } else {
                    that.parents('tr').remove();
                }
            });
            layer.close(index);
        });
        return false;
    });
    /**
     * 通用TR数据状态更新
     */
    $(document).on('click', '.j-tr-status,.hisi-tr-status', function() {
        var that = $(this),
            href = !that.attr('data-href') ? that.attr('href') : that.attr('data-href');
        layer.confirm('是否要修改此状态？', {title:false, closeBtn:0}, function(index){
            if (!href) {
                layer.msg('请设置data-href参数');
                return false;
            }
            $.get(href, function(res) {
                if (res.code == 0) {
                    layer.msg(res.msg);
                } else {
                    that.parents('tr').remove();
                }
            });
            layer.close(index);
        });
        return false;
    });

    /**
     * ajax请求操作
     * @attr href或data-href 请求地址
     * @attr refresh 操作完成后是否自动刷新
     * @class confirm confirm提示内容
     */
    $(document).on('click', '.j-ajax,.hisi-ajax', function() {
        var that = $(this), 
            href = !that.attr('data-href') ? that.attr('href') : that.attr('data-href'),
            refresh = !that.attr('refresh') ? 'true' : that.attr('refresh');
        if (!href) {
            layer.msg('请设置data-href参数');
            return false;
        }

        if (!that.attr('confirm')) {
            layer.msg('数据提交中...', {time:500000});
            $.get(href, {}, function(res) {
                layer.msg(res.msg, {}, function() {
                    if (refresh == 'true' || refresh == 'yes') {
                        if (typeof(res.url) != 'undefined' && res.url != null && res.url != '') {
                            location.href = res.url;
                        } else {
                            location.reload();
                        }
                    }
                });
            });
            layer.close();
        } else {
            layer.confirm(that.attr('confirm'), {title:false, closeBtn:0}, function(index){
                layer.msg('数据提交中...', {time:500000});
                $.get(href, {}, function(res) {
                    layer.msg(res.msg, {}, function() {
                        if (refresh == 'true') {
                            if (typeof(res.url) != 'undefined' && res.url != null && res.url != '') {
                                location.href = res.url;
                            } else {
                                location.reload();
                            }
                        }
                    });
                });
                layer.close(index);
            });
        }
        return false;
    });

    /**
     * 数据列表input编辑自动选中ids
     * @attr data-value 修改前的值
     */
    $('.j-auto-checked,hisi-auto-checked').blur(function(){
        var that = $(this);
        if(that.attr('data-value') != that.val()) {
            that.parents('tr').find('input[name="ids[]"]').attr("checked", true);
        }else{
            that.parents('tr').find('input[name="ids[]"]').attr("checked", false);
        };
        form.render('checkbox');
    });

    /**
     * input编辑更新
     * @attr data-value 修改前的值
     * @attr data-href 提交地址
     */
    $(document).on('focusout', '.j-ajax-input,.hisi-ajax-input',function(){
        var that = $(this), _val = that.val();
        if (_val == '') return false;
        if (that.attr('data-value') == _val) return false;
        if (!that.attr('data-href')) {
            layer.msg('请设置data-href参数');
            return false;
        }
        $.post(that.attr('data-href'), {val:_val}, function(res) {
            if (res.code == 1) {
                that.attr('data-value', _val);
            }
            layer.msg(res.msg);
        });
    });

    /**
     * 小提示
     */
    $('.tooltip').hover(function() {
        var that = $(this);
        that.find('i').show();
    }, function() {
        var that = $(this);
        that.find('i').hide();
    });

    /**
     * 列表页批量操作按钮组
     * @attr href 操作地址
     * @attr data-table table容器ID
     * @class confirm 类似系统confirm
     * @attr tips confirm提示内容
     */
    $(document).on('click', '.j-page-btns,.hisi-page-btns', function(){
        var that = $(this),
            query = '',
            code = function(that) {
                var href = that.attr('href') ? that.attr('href') : that.attr('data-href');
                var tableObj = that.attr('data-table') ? that.attr('data-table') : 'dataTable';
                if (!href) {
                    layer.msg('请设置data-href参数');
                    return false;
                }

                if ($('.checkbox-ids:checked').length <= 0) {
                    var checkStatus = table.checkStatus(tableObj);
                    if (checkStatus.data.length <= 0) {
                        layer.msg('请选择要操作的数据');
                        return false;
                    }
                    for (var i in checkStatus.data) {
                        if (i > 0) {
                            query += '&';
                        }
                        query += 'id[]='+checkStatus.data[i].id;
                    }
                } else {
                    if (that.parents('form')[0]) {
                        query = that.parents('form').serialize();
                    } else {
                        query = $('#pageListForm').serialize();
                    }
                }

                layer.msg('数据提交中...',{time:500000});
                $.post(href, query, function(res) {
                    layer.msg(res.msg, {}, function(){
                        if (res.code != 0) {
                            location.reload();
                        } 
                    });
                });
            };
        if (that.hasClass('confirm')) {
            var tips = that.attr('tips') ? that.attr('tips') : '您确定要执行此操作吗？';
            layer.confirm(tips, {title:false, closeBtn:0}, function(index){
                code(that);
                layer.close(index);
            });
        } else {
           code(that); 
        }
        return false;
    });

    /**
     * layui非静态table搜索渲染
     * @attr data-table table容器ID
     * @attr hisi-data table基础参数
     * @attr action 搜索请求地址
     */
    $('#system-table-search').submit(function() {
        var that = $(this), 
            arr = that.serializeArray(), 
            where = new Array(),
            dataTable = that.attr('data-table') ? that.attr('data-table') : 'dataTable',
            options = new Function('return '+ that.attr('hisi-data'))() || {page: true};
        
            for(var i in arr) {
                where[arr[i].name] = arr[i].value;
            }
        
        options.url = that.attr('action');
        options.where = where;
        
        table.reload(dataTable, options);
        return false;
    });
    
    /**
     * layui非静态table过滤渲染
     * @attr data-table table容器ID
     * @attr hisi-data table基础参数
     * @attr href 过滤请求地址
     */
    $(document).on('click', '.hisi-table-a-filter', function() {
        var that = $(this), dataTable = that.attr('data-table') ? that.attr('data-table') : 'dataTable',
        options = new Function('return '+ that.attr('hisi-data'))() || {page: true};
        
        options.url = that.attr('href');

        table.reload(dataTable, options);
        return false;
    });
    exports('global', {});
});