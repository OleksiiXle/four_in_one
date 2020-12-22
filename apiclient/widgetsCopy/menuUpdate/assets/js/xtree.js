const ITEM_CSS = 'item_css';
const ITEM_ACTIVE_CSS = 'item_active_css';
const ICON_CSS = 'icon_css';
const LI_CSS = 'li_css';
const UL_CSS = 'ul_css';
const ICON_OPEN  =  '<span class="glyphicon glyphicon-folder-open"></span>';
const ICON_CLOSE = '<span class="glyphicon glyphicon-folder-close"></span>';


var MENU_TREE = {
    tree_id: null,
    item_id : null,
    icon_id : null,
    li_id : null,
    ul_id : null,
    selected_id : 0,

    init: function(menu_id){
        this.tree_id = menu_id;
        this.item_id = menu_id + '_item_';
        this.icon_id = menu_id + '_icon_';
        this.li_id   = menu_id + '_li_';
        this.ul_id   = menu_id + '_ul_';

        this.drawDefaultTree(this.tree_id);
        //-- обработчики кликов на иконки и листья
        var that = this;
        $("#" + this.tree_id).on("click", "." + ICON_CSS, function () {
            that.clickIcon(this);
        });
        $("#" + this.tree_id).on("click", "." + ITEM_CSS, function () {
            that.clickItem(this);
        });
        //-- обработчики событий на кнопки редактирования, если они есть
        if ($("#actionButtons_" + this.tree_id).length > 0){
            $(document)
                .on("click", "#btn_" + that.tree_id + '_updateForm', function () {
                    that.menuUpdate();
                });


            $("#btn_" + this.tree_id + '_modalOpenMenuUpdate').on("click", function () {
                that.modalOpenMenuUpdate('update');
            });
            $("#btn_" + this.tree_id + '_appendChild').on("click", function () {
                that.modalOpenMenuUpdate('appendChild');
            });
            $("#btn_" + this.tree_id + '_appendBrother').on("click", function () {
                that.modalOpenMenuUpdate('appendBrother');
            });
            $("#btn_" + this.tree_id + '_levelUp').on("click", function () {
                that.treeModifyAuto('levelUp');
            });
            $("#btn_" + this.tree_id + '_levelDown').on("click", function () {
                that.treeModifyAuto('levelDown');
            });
            $("#btn_" + this.tree_id + '_moveUp').on("click", function () {
                that.treeModifyAuto('moveUp');
            });
            $("#btn_" + this.tree_id + '_moveDown').on("click", function () {
                that.treeModifyAuto('moveDown');
            });
            $("#btn_" + this.tree_id + '_deleteItem').on("click", function () {
                that.deleteItem();
            });

        }
    },

    //-- возвращает строку для рисования наименования с закрытой иконкой, если есть потомки, если нет - без иконки
    getItem: function (data) {
        var that = this;
        var result =
            '<li class="' + LI_CSS + '"' +
            ' id="' + this.li_id + data['id'] +  '"' +
            ' data-tree_id="' + this.tree_id + '" ' +
            ' data-id="' + data['id'] + '"' +
            ' data-parent_id="' + data['parent_id'] + '">';
        if (data['hasChildren']){
            result = result +
                '<a class="' + ICON_CSS + '"' +
                ' id="' + this.icon_id + data['id'] +  '"' +
                ' data-tree_id="' + this.tree_id + '"' +
                ' data-id="' + data['id'] + '" ' +
                ' data-parent_id="' + data['parent_id'] + '"' +
                // ' onClick="' + that.drawChildren(this.dataset.id)+ ';"'
                '>' +
                ICON_CLOSE  +
                '</a>  ';
        }
        result = result +
            '<a class="' + ITEM_CSS + '"' +
            ' id="' + this.item_id + data['id'] +  '"' +
            ' data-tree_id="' + this.tree_id + '" ' +
            ' data-id="' + data['id'] + '" ' +
            ' data-parent_id="' + data['parent_id'] +'" ' +
            // ' onClick="' + that.clickItem(this) + ';"'  +
            '> ' +
            data['name'] +
            '</a></li>' ;
        return result;
    },

    //-- возвращает строку для рисования иконки
    getIcon: function (data, state) {
        var picture = (state === 'open') ? ICON_OPEN : ICON_CLOSE;
        var result = '';
        if (data['hasChildren']){
            result = result +
                '<a class="' + ICON_CSS + '"' +
                ' id="' + this.icon_id + data['id'] +  '"' +
                ' data-tree_id="' + this.tree_id + '"' +
                ' data-id="' + data['id'] + '" ' +
                ' data-parent_id="' + data['parent_id'] + '"' +
                '>' +
                picture  +
                '</a>  ';
        }
        return result;
    },

    //-- рисует дефолтное дерево (вершины, у которых нет парентов
    drawDefaultTree: function (tree_id) {
        var that = this;
        $.ajax({
            url: _BASE_URL + '/widget/menux-get-default-tree',
            type: "POST",
            data: {'_csrf':_csrfT},
            dataType: 'json',
            beforeSend: function() {
                 preloader('show', 'mainContainer', 0);
            },
            complete: function(){
                 preloader('hide', 'mainContainer', 0);
            },
            success: function(response){
                if (response['status']) {
                    //     console.log(response);
                    var firstDiv = $("#" + tree_id);
                    $.each(response['data'], function(index, value){
                        $(firstDiv).append(that.getItem(value))
                    });
                } else {
                    objDump(response['data']);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown)            }
        });
    },

    //-- рисует потомков первого уровня узлу parent_id
    clickIcon: function (parent) {
        //   console.log(parent.innerHTML);
        var parent_id = parent.dataset.id;
        var that = this;
        switch (parent.innerHTML){
            case ICON_CLOSE:
                parent.innerHTML = ICON_OPEN;
                $.ajax({
                    url: _BASE_URL + '/widget/menux-get-children',
                    type: "POST",
                    data: {
                        'id' : parent_id,
                        '_csrf':_csrfT
                    },
                    dataType: 'json',
                    beforeSend: function() {
                         preloader('show', 'mainContainer', 0);
                    },
                    complete: function(){
                          preloader('hide', 'mainContainer', 0);
                    },
                    success: function(response){
                        if (response['status']) {
                            var children = response['data'];
                            if (children.length > 0){
                                //--найти родителя в этом дереве
                                var parent = $("#" + that.item_id + parent_id);
                                //    console.log(parent);
                                //-- добавить после него ul
                                parent.after(
                                    '<ul class="'+ UL_CSS + '"' +
                                    ' id="' + that.ul_id + parent_id +  '"' +
                                    '></ul>'
                                );
                                //-- получить єтот ul
                                var parent_ul = $("#" + that.ul_id + parent_id);
                                //-- добавить в ul потомков
                                $.each(children, function(index, value){
                                    $(parent_ul).append(that.getItem(value))
                                });

                            }
                            that.selectedIdChange(parent_id);
                        } else {
                            objDump(response['data']);
                        }
                    },
                    error: function (jqXHR, error, errorThrown) {
                        errorHandler(jqXHR, error, errorThrown)            }
                });
                break;
            case ICON_OPEN:
                parent.innerHTML = ICON_CLOSE;
                $("#" + that.ul_id + parent_id).remove();
                that.selectedIdChange(parent_id);
                break;
        }
    },

    clickItem: function (item) {
      //  console.log(item);
     //   alert('item ' + item.dataset.id);
        this.selectedIdChange(item.dataset.id);
    },

    //-- изменение текущего выбранного элемента
    selectedIdChange: function (new_id) {
        if (typeof clickItemFunction == 'function'){
            clickItemFunction(this.tree_id, new_id);
        }

        var that = this;
        //  $.post(_urlSetConserve, {'id' : new_id, 'type' : type, 'staffOrder_id': _treeParams[tree_id]['staffOrder_id'], 'tree_id' : tree_id});

        var old_selected_id = that.selected_id;
        var new_selected_id = new_id;
        var oldNode =$("#" + that.item_id + old_selected_id);
        var newNode =$("#" + that.item_id + new_selected_id);

        oldNode.removeClass(ITEM_ACTIVE_CSS).addClass(ITEM_CSS);
        newNode.removeClass(ITEM_CSS).addClass(ITEM_ACTIVE_CSS);
        that.selected_id = new_selected_id;

        var container = $("#" + that.tree_id),
            scrollTo = $('#' + new_selected_id);
        if (container.length > 0 && scrollTo.length > 0){
            //    console.log(container);
            //    console.log(scrollTo);
            container.stop().animate({
                scrollTop: scrollTo.offset().top -
                container.offset().top +
                container.scrollTop() - 200
            });
        }
        return true;
    },

    //************************************************************************************** редактирование дерева

    //-- открытие модального окна для редактирования, добавления потомка, добавления соседа
    modalOpenMenuUpdate : function (nodeAction) {
    //    alert(this.tree_id + ' modalOpenMenuUpdate ' + this.selected_id);
        var that = this;
        //var url = '/widget/menux-modal-open-menu-update?id=' + that.selected_id + '&menu_id=' + that.tree_id + '&nodeAction=' + nodeAction;
        var url = _BASE_URL + '/widget/menux-modal-open-menu-update';
        var data = {
            'id' : that.selected_id,
            'menu_id' : that.tree_id,
            'nodeAction' : nodeAction
        };
        $.ajax({
            url: url,
            type: "GET",
            data: data,
           // dataType: 'json',
            beforeSend: function() {
                preloader('show', 'mainContainer', 0);
            },
            complete: function(){
                preloader('hide', 'mainContainer', 0);
            },
            success: function(response){
                if ($('#main-modal-md').length > 0){
                    $('#main-modal-md').modal('show').find('.modal-content').html(response);
                } else{
                    var mHeader;
                    switch (nodeAction) {
                        case 'update':
                            mHeader = 'Изменить';
                            break;
                        case 'appendChild':
                            mHeader = 'Добавить потомка';
                            break;
                        case 'appendBrother':
                            mHeader = 'Добавить брата вниз';
                            break;


                    }
                    showModal(600,550, mHeader);
                    $('#xModalContent').html(response);

                }
             //   document.getElementById('modalHeader_lg').innerHTML = '<b>' + title + '</b>';
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown)            }
        });


            //  $('#main-modal-md').open();
        /*
        $('#main-modal-md').modal('show')
            .find('#modalContent_md')
            .load(url, function(response, status, xhr) {
                errorHandlerModal(xhr['status'], xhr, status);
            });
            */
        /*
        $('#main-modal-md')
            .on("click", "#btn_" + that.tree_id + '_updateForm', function () {
                that.menuUpdate();
            });
            */

        /*
        <button type="button" class="modal-trigger btn" data-target="main-modal-md">Show</button>
         */
    },

    //-- редактирование, добавление потомка, добавление соседа снизу
    menuUpdate: function () {
        var that = this;
        var node_action = $("#menux-nodeaction").val();
        var start_node = $("#menux-node1").val();
        var data = $("#menuMmodifyForm").serialize();
        var new_item;
        $.ajax({
            url: _BASE_URL + '/widget/menux-menu-update',
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function() {
                 preloader('show', 'mainContainer', 0);
            },
            complete: function(){
                  preloader('hide', 'mainContainer', 0);
            },
            success: function(response){
                if (response['status']) {
                 //   console.log(response['data']);
                    /*
hasChildren: false
id: 22
name: "2234234"
parent_id: "11"
sort: 2
                     */
                    switch (node_action){
                        case 'update':
                            new_item = $("#" + that.item_id + response['data']['id'])[0];
                            new_item.innerHTML = response['data']['name'];
                            that.clickItem(new_item);
                            break;
                        case 'appendChild':
                            var new_node_data = response['data']['newNode'];
                            var parent_node_data = response['data']['parentNode'];
                            var new_parent_icon = $("#" + that.icon_id + parent_node_data['id']);
                           // console.log("#" + that.icon_id + parent_node_data['id']);
                          //  console.log(new_parent_icon);
                            var new_node_li = that.getItem(new_node_data);
                            if (new_parent_icon.length > 0){
                                //-- у нового родителя node2_id уже есть иконка
                                if (new_parent_icon[0].innerHTML == ICON_OPEN){
                                    //-- иконка открыта и потомки показаны
                                    //-- найти первого ли потомка и перед ним нарисовать ли $node1_id
                                    var parent_ul = $("#" + that.ul_id + parent_node_data['id']);
                                    var children_li = parent_ul.find("li");
                                    var last_li_id = children_li[children_li.length - 1].dataset.id;
                                    $("#" + that.li_id + last_li_id).after(new_node_li);
                                    //console.log(first_child_li);

                                } else {
                                    //-- иконка закрыта и потомки скрыты
                                    //-- имитировать нажатие на иконку
                                    that.clickIcon(new_parent_icon[0]);
                                }

                            } else {
                                //-- у нового родителя node2_id еще нет иконки и нет потомков
                                //-- вставить закрытую иконку в ли перед итемом
                                //-- имитировать нажатие на иконку
                                var new_parent_item = $("#" + that.item_id + parent_node_data['id']);
                                new_parent_item.before(that.getIcon(parent_node_data, 'close'));
                                new_parent_icon = $("#" + that.icon_id + parent_node_data['id'])[0];
                                console.log("#" + that.icon_id + parent_node_data['id']);

                                that.clickIcon(new_parent_icon);
                            }





                            break;
                        case 'appendBrother':
                            var brother = $("#" + that.li_id + start_node);
                            var new_item_li = that.getItem(response['data']['newNode']);
                            brother.after(new_item_li)  ;
                            new_item = $("#" + that.item_id + response['data']['newNode']['id'])[0];
                            that.clickItem(new_item);
                            break;
                    }
                    if ($("#main-modal-md").length > 0){
                        $("#modal-content").html("");
                        $("#main-modal-md").modal("hide");
                    } else {
                        hideModal();
                    }
                } else {
                    console.log(response);
                    objDump(response['data']);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown)            }
        });

    },

    //-- операции без создания нового наименования
    treeModifyAuto : function (nodeAction) {
       // alert(this.tree_id + ' treeModifyAuto ' + nodeAction + ' ' + this.selected_id);
        var node1_id, node2_id, node1_li, node2_li;
        var that = this;
        node1_id = this.selected_id;
        node1_li = $("#" + that.li_id + node1_id);
       // console.log('node1_li:');
      //  console.log(node1_li);
        switch (nodeAction){
            case 'moveUp':
                //--- move up
                node2_li = node1_li.prev("." + LI_CSS);
                if (node2_li.length > 0 ) {
                    node2_id = node2_li[0].dataset['id'];//-- сосед сверху
                } else {
                    alert('Операция не возможна');
                    return;
                }
                break;
            case 'moveDown':
                //--- move down
            case 'levelDown':
                //-- сделать меня ($node1_id) первым сыном моего младшего брата ($node2_id)

                //-- сделать $node1_id из соседа сверху $node2_id - его первым потомком
                node2_li = node1_li.next("." + LI_CSS);
                if (node2_li.length > 0 ) {
                    node2_id = node2_li[0].dataset['id'];//-- сосед сверху
                } else {
                    alert('Операция не возможна');
                    return;
                }
                break;
            case 'levelUp':
                //-- сделать $node1_id из потомка $node2_id - его соседом сверху
                node2_li = $("#" + that.li_id + node1_li[0].dataset['parent_id']);
                if (node2_li.length > 0 ) {
                    node2_id = node2_li[0].dataset['id'];//-- сосед сверху
                } else {
                    alert('Операция не возможна');
                    return;
                }
                break;
        }
        $.ajax({
            url: _BASE_URL + '/widget/menux-tree-modify-auto',
            type: "POST",
            data: {
                'node1_id' : node1_id,
                'node2_id' : node2_id,
                'nodeAction' : nodeAction,
                '_csrf':_csrfT
            },
            dataType: 'json',
            beforeSend: function() {
                 preloader('show', 'mainContainer', 0);
            },
            complete: function(){
                 preloader('hide', 'mainContainer', 0);
            },
            success: function(response){
                if (response['status']) {
                    console.log(response['data']);
                    switch (nodeAction){
                        case 'moveUp':
                            $(node2_li).before(node1_li);
                            break;
                        case 'moveDown':
                            $(node1_li).before(node2_li);
                            break;
                        case 'levelDown':
                            //-- сделать меня ($node1_id) первым сыном моего младшего брата ($node2_id)
                            //-- сделать $node1_id из соседа сверху $node2_id - его первым потомком
                            var new_parent_icon = $("#" + that.icon_id + node2_id);

                            node1_li[0].dataset.parent_id = node2_li[0].dataset.id;

                            if (new_parent_icon.length > 0){
                                //-- у нового родителя node2_id уже есть иконка
                                if (new_parent_icon[0].innerHTML == ICON_OPEN){
                                    //-- иконка открыта и потомки показаны
                                    //-- найти первого ли потомка и перед ним нарисовать ли $node1_id
                                    var parent_ul = $("#" + that.ul_id + node2_id);
                                    var first_child_li = parent_ul.find("li");
                                    $(first_child_li[0]).before(node1_li);

                                } else {
                                    //-- иконка закрыта и потомки скрыты
                                    //-- имитировать нажатие на иконку
                                    node1_li.remove();
                                    that.clickIcon(new_parent_icon[0]);
                                }

                            } else {
                                //++++
                                //-- у нового родителя node2_id еще нет иконки и нет потомков
                                //-- вставить закрытую иконку в ли перед итемом
                                //-- имитировать нажатие на иконку
                                var new_parent_item = $("#" + that.item_id + node2_id);
                                new_parent_item.before(that.getIcon(response['data']['node2']));
                                new_parent_icon = $("#" + that.icon_id + node2_id)[0];
                                node1_li.remove();
                                that.clickIcon(new_parent_icon);
                            }
                            break;
                        case 'levelUp':
                            //-- сделать $node1_id из потомка $node2_id - его соседом сверху
                            $(node2_li).before(node1_li);
                            node1_li[0].dataset.parent_id = node2_li[0].dataset.parent_id;
                            if (!response['data']['node2']['hasChildren']){
                                $("#" + that.icon_id + node2_id).remove();
                            }

                            break;
                    }
                } else {
                    console.log(response['data']);
                    objDump(response['data']);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown)            }
        });

    },

    //--удаление наименования вместе с потомками
    deleteItem : function () {
    //  alert(this.tree_id + ' deleteItem ' + this.selected_id);
        if (confirm('Подтвердите удаление')){
            var that = this;
            $.ajax({
                url: _BASE_URL + '/widget/menux-delete',
                type: "POST",
                data: {
                    '_csrf':_csrfT,
                    'node1_id' : that.selected_id
                },
                dataType: 'json',
                beforeSend: function() {
                    preloader('show', 'mainContainer', 0);
                },
                complete: function(){
                    preloader('hide', 'mainContainer', 0);
                },
                success: function(response){
                    if (response['status']) {
                        var removed_item_li = $("#" + that.li_id + that.selected_id);
                        var prev_item_li = removed_item_li.prev('LI');
                        var parent_id = removed_item_li[0].dataset.parent_id;
                        var new_selected_id = 0;

                        removed_item_li.remove();

                        if ((typeof response['data']['node2'] === 'object') && !response['data']['node2']['hasChildren']){

                            $("#" + that.icon_id + response['data']['node2']['id'] ).remove();
                        }

                        if (prev_item_li.length > 0){
                            new_selected_id = prev_item_li[0].dataset.id;
                        } else {
                            if (parent_id != 0){
                                new_selected_id = parent_id;
                            }
                        }
                        that.selectedIdChange(new_selected_id);
                    } else {
                        objDump(response['data']);
                    }
                },
                error: function (jqXHR, error, errorThrown) {
                    errorHandler(jqXHR, error, errorThrown)            }
            });

        }

    },




};


/*
$(document).ready ( function(){
    var tree1 = Object.create(MENU_TREE);
    tree1.init(_menu_id);
});
*/

