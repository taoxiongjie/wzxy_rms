jQuery(function($) {


    var del_node = '';
    var del_link = '';
//点击弹出盘点EXCEL浏览页面
    $(document).on("click", ".excle_view_btn", function () {

        var status = $(this).attr('data-status');
        if (status == 0) {
            artdialog_warning('对不起，该盘点任务还未完成，不能查看盘点结果详情!');
        }
        else {
            var projectid = $('#project_id').val();
            var url = GV.ROOT + "Inventory/inventory_excel_view/projectId/" + projectid + "/taskId/" + $(this).attr('data-id');
            var title = "历史盘点浏览";
            open_iframe_task_management(url, title);
        }

    });


//点击选择设备类型EXCEL浏览页面

    $(document).on("click", ".sheet_index", function () {

        var number = $(this).attr('data-id');
        console.log(number);
        var taskId = $('#taskId').val();
        var projectid = $('#project_id').val();
        var url = GV.ROOT + "Inventory/inventory_excel_view/projectId/" + projectid + "/taskId/" + taskId + "/number/" + $(this).attr('data-id');
        var title = "历史盘点浏览";
        window.location.href = url;
    });

//点击弹出盘点任务表单页面

    $(document).on("click", ".task_management_item", function () {
        var projectid = $('#consolepageProjectId').val();
        var url = GV.ROOT + "Inventory/task_management_inventory/projectId/" + projectid + "/taskId/" + $(this).attr('data-id');
        var title = "处理盘点任务:" + $(this).attr('data-title');
        open_iframe_task_management(url, title);

    });
//点击弹出上架页面

    $(document).on("click", ".on_open_item", function () {
        var projectid = $('#consolepageProjectId').val();
        var url = GV.ROOT + "Inventory/task_management_do2/projectId/" + projectid + "/taskId/" + $(this).attr('data-id');
        var title = "处理任务:" + $(this).attr('data-title');
        open_iframe_task_management_ifram(url, title);

    });

    /*//点击一般任务类型弹出页面（下架、迁出、报修）
    $(".on_open_item_common_task").live("click",function(){
        var projectid= $('#consolepageProjectId').val();
        var url=GV.ROOT+"Inventory/task_management_do/projectId/"+projectid+"/taskId/"+$(this).attr('data-id');
        var title="处理任务:"+$(this).attr('data-title');
        open_iframe_task_management_ifram(url, title);

    });*/

//弹出盘点任务表单页面
    function open_iframe_task_management(url, title, options) {
        url = encodeURI(encodeURI(url));
        var params = {
            type: 2,
            title: [title, 'height:30px;text-align:center;color:#fff;font-size:14px;background:#333;border-bottom:none'],
            shadeClose: true,
            skin: 'demo-class',
            shade: [0.5, '#000000'],
            area: ['80%', '80%'],
            scrollbar: false,
            content: url,
            shadeClose: false
        };
        params = options ? $.extend(params, options) : params;

        Wind.css('layer');

        Wind.use("layer", function () {
            layer.open(params);
        });


    }

//弹出任务表单页面
    function open_iframe_task_management_ifram(url, title, options) {
        url = encodeURI(encodeURI(url));
        console.log(url);
        var params = {
            type: 2,
            title: [title, 'height:30px;text-align:center;color:#fff;font-size:14px;background:#333;border-bottom:none'],
            shadeClose: true,
            skin: 'demo-class',
            shade: [0.5, '#000000'],
            area: ['880px', '520px'],
            scrollbar: false,
            content: url,
            shadeClose: false
        };
        params = options ? $.extend(params, options) : params;

        Wind.css('layer');

        Wind.use("layer", function () {
            layer.open(params);
        });


    }

//提交线路信息和节点信息
    function saveWirngInfo() {
        var line_name = $("#line_name_edit").val();
        var ids = $("#line_val_edit").val();
        var projectId = $("#consolepageProjectId").val();
        var device_ids = new Array();
        $(".wiring_edit .device_name").each(function () {
            var device_info = {
                device_name: $(this).text(),
                device_id: $(this).attr("value"),
                handle_id: $(this).attr("handle_id"),
                parent_handle: $(this).attr("parent_handle"),
                port_id: $(this).next().val(),
                id: $(this).parent().attr("id")
            };
            device_ids.push(device_info);
        });
        var node_to_nodes = new Array();
        $(".wiring_edit .wiring_name").each(function () {
            var node_info = {
                bind_id: $(this).attr("value"),
                name: $(this).attr("name"),
                id: $(this).attr("id")
            };
            // alert(node_info.id);
            node_to_nodes.push(node_info);
        });


        if (line_name == "未编辑线路") {
            alert("请输入线路名称");
        } else {
            $.ajax({
                cache: true,
                type: "POST",
                url: GV.ROOT + "index/saveWiringInfo",
                data: {
                    "line_name": line_name, "ids": ids, "device_ids": device_ids, "node_to_nodes": node_to_nodes,
                    "del_node": del_node, "del_link": del_link, "projectId": projectId
                },
                error: function (request) {
                    alert('数据出错');
                },
                success: function (data) {
                    if (data) {
                        $(".backDetal_btn").click();
                        alert("保存成功");
                    } else {
                        alert("保存失败");
                    }
                }
            });
        }
    }

    function getWirngListInfo() {
        var projectId = $("#consolepageProjectId").val();
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getWiringList",
            data: {"projectId": projectId},
            async: false,
            error: function (request) {
                alert('数据出错');
            },
            success: function (data) {
                console.log(data);
                var html = data.html;
                $("#Wiring-tree").html(html);
                $("#Wiring-tree").treeview({collapsed: true, persist: "cookie", cookieId: "Wiring-tree"});
            }
        });
    }

    function getWirngDetalInfo() {
        var projectId = $("#consolepageProjectId").val();
        var line_val = $("#line_val").attr("value").split(",");
        var line_id = line_val[3];
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getWiringDetalInfo",
            data: {"line_id": line_id, "projectId": projectId},
            async: false,
            error: function (request) {
                alert('数据出错');
            },
            success: function (data) {
                var node = data.line_node_array;
                var link = data.link_array;
                var totle_line_name = data.line_name;
                $("#line_name").val(totle_line_name);
                var html = '';
                if (node.length > 0) {
                    for (var i = 0; i < node.length; i++) {
                        if (i == 0) {
                            html += '<div class="wiring_item" id="' + node[i].id + '">'
                                + '<div class="device_name" value="' + node[i].device_id + '" handle_id="' + node[i].handle_id
                                + '" parent_handle="' + node[i].parent_handle + '">' + node[i].device_name + '</div>'
                                + '<select class="port_name" disabled="disabled"><option value=' + node[i].port_id + '>' + node[i].port_name + '</option></select>'
                                + '</div>';
                        } else {
                            html += '<span class="wiring_name" id="' + link[i - 1].id + '" value="' + link[i - 1].bind_id + '" name="' + link[i - 1].name + '">'
                                + link[i - 1].bind_id + ' ' + link[i - 1].name + '</span>'
                                + '<div class="wiring_item" id="' + node[i].id + '">'
                                + '<div class="device_name" value="' + node[i].device_id + '" handle_id="' + node[i].handle_id
                                + '" parent_handle="' + node[i].parent_handle + '">' + node[i].device_name + '</div>'
                                + '<select class="port_name" disabled="disabled"><option value="' + node[i].port_id + '">' + node[i].port_name + '</option></select>'
                                + '</div>';
                        }
                    }
                } else {
                    html = '<div class="wiring_item">'
                        + '<div class="device_name">设备名称</div>'
                        + '<select class="port_name" disabled="disabled"><option value="0">端口名称</option></select>'
                        + '</div>';
                }

                $(".wiring_detal .wiring_list").html(html);
            }
        });
    }

    function getWirngEditInfo() {
        var projectId = $("#consolepageProjectId").val();
        var line_val = $("#line_val_edit").attr("value").split(",");
        var line_id = line_val[3];
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getWiringDetalInfo",
            data: {"line_id": line_id, "projectId": projectId},
            async: false,
            error: function (request) {
                alert('数据出错');
            },
            success: function (data) {
                var node = data.line_node_array;
                var link = data.link_array;
                var totle_line_name = data.line_name;
                $("#line_name_edit").val(totle_line_name);
                var html = '';
                if (node.length > 0) {
                    for (var i = 0; i < node.length; i++) {
                        if (i == 0) {
                            html += '<div class="wiring_item" id="' + node[i].id + '">'
                                + '<div class="device_name" value="' + node[i].device_id + '" handle_id="' + node[i].handle_id
                                + '" parent_handle="' + node[i].parent_handle + '">' + node[i].device_name + '</div>'
                                + '<select class="port_name" onchange="get_port_info_to_webgl(this)"><option value=' + node[i].port_id + '>' + node[i].port_name + '</option></select>'
                                + '<button class="add">+</button>'
                                + '<button class="del">-</button></div>';
                        } else {
                            html += '<span class="wiring_name" id="' + link[i - 1].id + '" value="' + link[i - 1].bind_id + '" name="' + link[i - 1].name + '">'
                                + link[i - 1].bind_id + ' ' + link[i - 1].name + '</span>'
                                + '<div class="wiring_item" id="' + node[i].id + '">'
                                + '<div class="device_name" value="' + node[i].device_id + '" handle_id="' + node[i].handle_id
                                + '" parent_handle="' + node[i].parent_handle + '">' + node[i].device_name + '</div>'
                                + '<select class="port_name" onchange="get_port_info_to_webgl(this)"><option value="' + node[i].port_id + '"">' + node[i].port_name + '</option></select>'
                                + '<button class="add">+</button>'
                                + '<button class="del">-</button></div>';
                        }
                    }
                } else {
                    html = '<div class="wiring_item">'
                        + '<div class="device_name">设备名称</div>'
                        + '<select class="port_name" onchange="get_port_info_to_webgl(this)"><option value="0">端口名称</option></select>'
                        + '<button class="add">+</button>'
                        + '<button class="del">-</button></div>';
                }

                $(".wiring_edit .wiring_list").html(html);
            }
        });
    }

    function getPortInfo(device_id, port_id) {
        var projectId = $("#consolepageProjectId").val();
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getPortInfoAjax",
            data: {"device_id": device_id, "projectId": projectId},
            dataType: "json",
            async: false,
            error: function (request) {
                alert("请求端口数据出错");
            },
            success: function (data) {
                var port = data.port;
                var html = "";
                for (var i = 0; i < port.length; i++) {
                    if (port_id == port[i].port_id) {
                        html += "<option value=" + port[i].port_id + " selected=\"selected\">" + port[i].port_name + "</option>";
                    } else {
                        html += "<option value=" + port[i].port_id + ">" + port[i].port_name + "</option>";
                    }
                }
                $(".wiring_edit .device_name.current").next().html(html);
            }
        });
    }

    function searchLinkbyName() {
        var projectId = $("#consolepageProjectId").val();
        var link_name = $("#link_search").val();
        if (link_name) {
            $(".wiring_bottom_btn.view").hide();
            $.ajax({
                cache: true,
                type: "POST",
                url: GV.ROOT + "index/linkSearch",
                data: {"link_name": link_name, "projectId": projectId},
                dataType: "json",
                error: function (request) {
                    // alert("查找线路信息出错");
                },
                success: function (data) {
                    var html = data.html;
                    if (html) {
                        $("#Wiring-tree").html(html);
                        $("#Wiring-tree").treeview({collapsed: false});
                    } else {
                        $("#Wiring-tree").html("搜索结果为0");
                    }
                }
            });
        } else {
            getWirngListInfo();//重新获取线路列表树
            $(".wiring_bottom_btn.view").show();
        }
    }

    function get_link_info_to_webgl(link_id) {
        var projectId = $("#consolepageProjectId").val();
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getLinkAndNodeInfoToWebgl",
            data: {"link_id": link_id, "projectId": projectId},
            dataType: "json",
            error: function (request) {
                alert("请求端口数据出错");
            },
            success: function (data) {
                var link_info = data.link_info;
                web_transmit_webgl_with_location_link(link_info);
            }
        });

    }

    function get_node_info_to_webgl(device_id, port_id) {
        var projectId = $("#consolepageProjectId").val();
        $.ajax({
            cache: true,
            type: "POST",
            url: GV.ROOT + "index/getNodeInfoToWebgl",
            data: {"device_id": device_id, "projectId": projectId},
            dataType: "json",
            error: function (request) {
                alert("请求端口数据出错");
            },
            success: function (data) {
                var device_info = data.device_info;
                device_info["port_id"] = port_id;
                web_transmit_webgl_with_location_node(device_info);
            }
        });
    }
});

