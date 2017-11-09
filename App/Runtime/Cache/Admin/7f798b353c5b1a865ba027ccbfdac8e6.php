<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="Bookmark" href="/favicon.ico">
    <link rel="Shortcut Icon" href="/favicon.ico" />
    <link href="/xcx_yamaxun/Public/admin/css/main.css" rel="stylesheet" type="text/css" />
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/lib/html5shiv.js"></script>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/lib/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/xcx_yamaxun/Public/admin/static/h-ui/css/H-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="/xcx_yamaxun/Public/admin/static/h-ui.admin/css/H-ui.admin.css" />
    <link rel="stylesheet" type="text/css" href="/xcx_yamaxun/Public/admin/lib/Hui-iconfont/1.0.8/iconfont.css" />
    <link rel="stylesheet" type="text/css" href="/xcx_yamaxun/Public/admin/static/h-ui.admin/skin/default/skin.css" id="skin" />
    <link rel="stylesheet" type="text/css" href="/xcx_yamaxun/Public/admin/static/h-ui.admin/css/style.css" />
    <!--[if IE 6]>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/js/jquery.js"></script>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/js/action.js"></script>
    <script type="text/javascript" src="/xcx_yamaxun/Public/plugins/xheditor/xheditor-1.2.1.min.js"></script>
    <script type="text/javascript" src="/xcx_yamaxun/Public/plugins/xheditor/xheditor_lang/zh-cn.js"></script>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/js/jCalendar.js"></script>

    <style>
        <?php $width=round($img['width']*0.6+6); $height=round( $width*$img['height'] / $img['width']); ?>li {
            list-style-type: none;
        }
        
        .dx1 {
            float: left;
            margin-left: 17px;
            margin-bottom: 10px;
        }
        
        .dx2 {
            color: #090;
            font-size: 16px;
            border-bottom: 1px solid #CCC;
            width: 100% !important;
            padding-bottom: 8px;
        }
        
        .dx3 {
            width: 120px;
            margin: 5px auto;
            border-radius: 2px;
            border: 1px solid #b9c9d6;
            display: block;
        }
        
        .dx4 {
            border-bottom: 1px solid #eee;
            padding-top: 5px;
            width: 100%;
        }
        
        .img-err {
            position: relative;
            top: 2px;
            left: 82%;
            color: white;
            font-size: 20px;
            border-radius: 16px;
            background: #c00;
            height: 21px;
            width: 21px;
            text-align: center;
            line-height: 20px;
            cursor: pointer;
        }
        
        .btn {
            height: 25px;
            width: 60px;
            line-height: 24px;
            padding: 0 8px;
            background: #24a49f;
            border: 1px #26bbdb solid;
            border-radius: 3px;
            color: #fff;
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            outline: none;
            -webkit-box-shadow: #666 0px 0px 6px;
            -moz-box-shadow: #666 0px 0px 6px;
        }
        
        .btn:hover {
            border: 1px #0080FF solid;
            background: #D2E9FF;
            color: red;
            -webkit-box-shadow: rgba(81, 203, 238, 1) 0px 0px 6px;
            -moz-box-shadow: rgba(81, 203, 238, 1) 0px 0px 6px;
        }
        
        .cls {
            background: #24a49f;
        }
    </style>

    <title>添加产品</title>
</head>

<body>
    <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 产品管理 <span class="c-gray en">&gt;</span> 添加产品 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);"
            title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class="page-container">
        <form class="form form-horizontal" action="?id=<?php echo ($id); ?>&page=<?php echo ($page); ?>&type=<?php echo ($type); ?>&name=<?php echo ($name); ?>&shop_id=<?php echo ($shop_id); ?>" method="post" onsubmit="return ac_from();" enctype="multipart/form-data">
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>产品名称：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="产品名称" name="name" id="name" value="<?php echo ($pro_allinfo["name"]); ?>">
                </div>
            </div>

            <!-- <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>广告语：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="广告语" name="intro" id="intro" value="<?php echo ($pro_allinfo["name"]); ?>">
                </div>
            </div> -->

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>选择分类：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <select class="inp_1 input-text" name="cid" id="cid" style="width:150px;margin-right:5px;">
	                    <!-- 遍历 -->
	                    <option value="">一级分类</option>
	                    <?php if(is_array($cate_list)): $i = 0; $__LIST__ = $cate_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>" <?php if($v["id"] == $pro_allinfo['cid']): ?>selected="selected"<?php endif; ?>>-- <?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	                    <!-- 遍历 -->
					</select>
                    <span id="catedesc" style="color:red;font-size: 12px;">&nbsp;&nbsp; * 必选项</span>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>课时：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="课 时" name="class" id="class" value="<?php echo ($pro_allinfo["class"]); ?>">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>原价：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="原 价" name="price" id="price" value="<?php echo ($pro_allinfo["price"]); ?>">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠价：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="优惠价" name="price_yh" id="price_yh" value="<?php echo ($pro_allinfo["price_yh"]); ?>">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red"></span>代理价：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="代理价" name="price_dl" id="price_dl" value="<?php echo ($pro_allinfo["price_dl"]); ?>">
                </div>
            </div>

            <!-- <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>赠送积分：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="赠送积分" name="price_jf" id="price_jf" value="<?php echo ($pro_allinfo["price_jf"]); ?>">
                </div>
            </div> -->
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>产品编号：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="产品编号" name="pro_number" id="pro_number" value="<?php echo ($pro_allinfo["pro_number"]); ?>">
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>库存：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="库存" name="num" id="num" value="<?php echo $pro_allinfo['num']==0 ? 999999 : $pro_allinfo['num']; ?>">
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>缩略图，图片大小230*230</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <?php if ($pro_allinfo['photo_x']) { ?>
                    <img src="/xcx_yamaxun/Data/<?php echo $pro_allinfo['photo_x']; ?>" width="80" height="80" style="margin-bottom: 3px;" />
                    <br />
                    <?php } ?>
                    <input type="file" name="photo_x" id="photo_x" />
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>大 图，图片大小600*600</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <?php if ($pro_allinfo['photo_d']) { ?>
                    <img src="/xcx_yamaxun/Data/<?php echo $pro_allinfo['photo_d']; ?>" width="125" height="125" style="margin-bottom: 3px;" />
                    <br />
                    <?php } ?>
                    <input type="file" name="photo_d" id="photo_d" />
                </div>
            </div>

            <!-- 
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>上传产品详情轮播图: 600*600的图片</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <?php if (is_array($img_str)) { ?>
                    <li>
                        <div class="d1">已上传：</div>
                        <?php foreach ($img_str as $v) { ?>
                        <div>
                            <div class="img-err" title="删除" onclick="del_img('<?php echo $v; ?>',this);">×</div>
                            <img src="<?php echo '/xcx_yamaxun/Data/'.$v; ?>" width="125" height="125">
                        </div>
                        <?php } ?>
                    </li>
                    <?php } ?>
                    <li id="imgs_add">
                        <div class="d1">轮播图:</div>
                        <div>
                            <input type="file" name="files[]" style="width:160px;" />
                        </div>
                    </li>
                    <li>
                        <div class="d1">&nbsp;</div>
                        <div>
                            &nbsp;<span class="btn cls" style="background:#D0D0D0; width:40px; color:black;" onclick="upadd();">添加</span>
                        </div>
                    </li>
                </div>
            </div>
			 -->

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>关联产品：</label>
                <div>
                    <div id="child_info" class="product-add">
                        <table class="table table-border table-bordered table-bg">
                            <thead>
                                <tr class="text-c">
                                    <th>分类</th>
                                    <th>产品</th>
                                    <th>数量</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            	
                            <tbody id="p_list">
                            	<?php if(is_array($relate_list)): $i = 0; $__LIST__ = $relate_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><tr class="text-c" id="pro_<?php echo ($r["id"]); ?>">
					                <td><?php echo ($r["catname"]); ?></td>
					                <td><?php echo ($r["name"]); ?></td>
					               	<td><?php echo ($r["count"]); ?></td>
					                <td>
					                	<input type="hidden" name="relate_id[]" value="<?php echo ($r["id"]); ?>" />
					                	<input type="hidden" name="count[]" value="<?php echo ($r["count"]); ?>" />
					                    <button onclick="$(this).parent().parent().remove();">删除</button>
					                </td>
					            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                            
                                <tr>
                                    <td style="text-align: center;">
                                        <select id="category" class="inp_1 input-text" onchange="getproduct();">
                                            <!-- 遍历 -->
                                            <option value="">分类列表</option>
                                                <?php if(is_array($cate_list)): $i = 0; $__LIST__ = $cate_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                            <!-- 遍历 -->
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <select id="product" class="inp_1 input-text">
                                            <!-- 遍历 -->
                                            <option value="">产品列表</option>
                                            <?php if(is_array($product_list)): $i = 0; $__LIST__ = $product_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                            <!-- 遍历 -->
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="text" class="input-text input-number" style="width: 100px;" placeholder="数量" id="count" />
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="javascript:void(0);" onclick="addproduct();">增加</a>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>产品介绍：</label>
                <div class="">
                    <textarea class="inp_1 inp_2" name="content" id="content" /><?php echo ($pro_allinfo["content"]); ?></textarea>
                </div>
            </div>

            <!-- 
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>人气：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="text" class="input-text" placeholder="人气" name="renqi" id="renqi" value="<?php echo (int)$pro_allinfo['renqi']; ?>">
                </div>
            </div>


            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>新上市：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="radio" name="is_show" value="1" <?php echo $pro_allinfo[ 'is_show']==1 ? 'checked="checked"' : null?>/> 是 &nbsp;
                    <input type="radio" name="is_show" value="0" <?php echo $pro_allinfo[ 'is_show']!=1 ? 'checked="checked"' : null?> /> 否
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>热销产品：</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="radio" name="is_hot" value="1" <?php echo $pro_allinfo[ 'is_hot']==1 ? 'checked="checked"' : null?>/> 是 &nbsp;
                    <input type="radio" name="is_hot" value="0" <?php echo $pro_allinfo[ 'is_hot']!=1 ? 'checked="checked"' : null?> /> 否
                </div>
            </div>


            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>品牌图片，图片大小120*120</label>
                <div class="formControls col-xs-8 col-sm-3">
                    <input type="file" name="file" id="file" value="">
                </div>
            </div>
			 -->

            <div class="row cl">
                <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                    <input class="btn btn-primary radius" type="submit" name="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                    <input type="hidden" name="pro_id" id='pro_id' value="<?php echo ($pro_allinfo["id"]); ?>">
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="/xcx_yamaxun/Public/admin/js/product.js"></script>

    <!--_footer 作为公共模版分离出去-->




    <script>
        //初始化编辑器
        $('#content').xheditor({
            skin: 'nostyle',
            upImgUrl: '<?php echo U("Upload/xheditor");?>'
        });

        function upadd(obj) {
            //alert('aaa');
            $('#imgs_add').append('<div>&nbsp;&nbsp;<input type="file" style="width:160px;" name="files[]" /><a onclick="$(this).parent().remove();" class="btn cls" style="background:#D0D0D0; width:40px; color:black;"">删除</a></div>');
            return false;
        }

        /* function getcid() {
            var cateid = $('#cateid').val();
            $.post('<?php echo U("getcid");?>', {
                cateid: cateid
            }, function(data) {
                if (data.catelist != '') {
                    var htmls = '<option value="">二级分类</option>';
                    var cate = data.catelist;
                    for (var i = 0; i < cate.length; i++) {
                        htmls += '<option value="' + cate[i].id + '">-- ' + cate[i].name + '</option>';
                    }
                    $('#cid').html(htmls);
                    $('#catedesc').html('&nbsp;&nbsp; * 必选项');
                } else {
                    $('#cid').html('<option value="">二级分类</option>');
                    $('#catedesc').html('&nbsp;&nbsp; * 该分类下还没有二级分类，请先添加');
                }
            }, "json");
        } */

        function getproduct() {
            var cateid = $('#category').val();
            $.post('<?php echo U("getproduct");?>', {
                cateid: cateid
            }, function(data) {
                if (data.prolist != '') {
                    var htmls = '<option value="">产品列表</option>';
                    var product = data.prolist;
                    for (var i = 0; i < product.length; i++) {
                        htmls += '<option value="' + product[i].id + '">' + product[i].name + '</option>';
                    }
                    $('#product').html(htmls);
                } else {
                    $('#product').html('<option value="">产品列表</option>');
                }
            }, "json");
        }

        //添加关联商品
        function addproduct(){
        	var category = $('#category').find("option:selected").text();
        	var product = $('#product').find("option:selected").text();
        	var cid = $('#category').val();
        	var pro_id = $('#product').val();
        	var count = $('#count').val();
        	if(count==0 || count==null){
        		alert('请填写数量');
                return false;
        	}
        	if($('#pro_'+pro_id).html()==null){
        		var html= '<tr class="text-c" id="pro_'+pro_id+'">'+
			                '<td>'+category+'</td>'+
			                '<td>'+product+'</td>'+
			               	'<td>'+count+'</td>'+
			                '<td>'+
			                	'<input type="hidden" name="relate_id[]" value="'+pro_id+'" />'+
			                	'<input type="hidden" name="count[]" value="'+count+'" />'+
			                    '<button onclick="$(this).parent().parent().remove();">删除</button>'+
			                '</td>'+
			            '</tr>';
				$('#p_list').append(html);
        	}else{
        		alert('产品已添加');
                return false;
        	}
        }
        
        //图片删除
        function del_img(img, obj) {
            var pro_id = $('#pro_id').val();
            if (confirm('是否确认删除？')) {
                $.post('<?php echo U("img_del");?>', {
                    img_url: img,
                    pro_id: pro_id
                }, function(data) {
                    if (data.status == 1) {
                        $(obj).parent().remove();
                        return false;
                    } else {
                        alert(data.err);
                        return false;
                    }
                }, "json");
            };
        }

        function ac_from() {

            var name = document.getElementById('name').value;
            if (name.length < 1) {
                alert('产品名称不能为空');
                return false;
            }

            var cid = parseInt(document.getElementById("cid").value);
            if (!cid) {
                alert("请选择分类.");
                return false;
            }

            //  var pid=parseInt(document.getElementById("shop_id").value);
            // if(isNaN(pid) || pid<1){
            // 	alert("请选择所属商家");
            // 	return false;
            // }

        }
    </script>

</body>

</html>