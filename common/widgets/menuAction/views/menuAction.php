<?php
$style = ($offset < 0) ? 'style= "display: none;  margin-left: ' . $offset . 'px;";' : 'style="display: none;"';
$needConfirm = (!empty($confirm))
    ? " data-confirm='$confirm' data-method='post' "
    : "";
/*
<a href="/adminx/translation/delete?tkey=1" title="Удалить" data-confirm="Подтвердите удаление" data-method="post"><span class="glyphicon glyphicon-trash"></span></a>
 */
?>
<style>
    .route {
        cursor: pointer;
    }
    .menu-icon{
    }
    .menu-action{
        padding: 0!important;
        margin: 0!important;
    }
    .items{
        position: absolute;
        background: #eeeeee;
        border: 2px solid #bdbdbd; /* Параметры границы */
     /*   opacity: 1;*/
        padding: 20px;
        margin-top: -5px;
        z-index: 2;
    }



</style>
    <ul class="menu-action"
        onmouseover="$(this).find('.items').show();"
        onmouseout="$(this).find('.items').hide();"
        style="margin-left: 0; /* Отступ слева в браузере IE и Opera */
               padding-left: 0; /* Отступ слева в браузере Firefox, Safari, Chrome */"
    >
        <span class="menu-icon <?=$icon;?> " ></span>
        <li class="items" <?=$style;?>>
            <?php foreach ($items as $text => $route):?>
                <?php if (is_array($route)):?>
                    <?php if (isset($route['confirm']) && !empty($route['confirm'])) :?>
                        <a class="route no-pjax" href="<?=$route['route'];?>" data-confirm="<?=$route['confirm'] ?>" data-method="post">
                        <span>
                        <span class="<?=$route['icon']?>"></span>
                        <span style="padding-left: 5px"><?=$text;?></span>
                        </span>
                        </a>
                    <?php else:?>
                        <a class="route no-pjax" href="<?=$route['route'];?>">
                        <span>
                        <span class="<?=$route['icon']?>"></span>
                        <span style="padding-left: 5px"><?=$text;?></span>
                        </span>
                        </a>
                    <?php endif;?>
                <?php else:?>
                    <a class="route no-pjax" href="<?=$route;?>"  <?=$needConfirm?> ><?=$text;?></a>
                <?php endif;?>
                <br>
            <?php endforeach;?>
        </li>

    </ul>

<script>
    function drawMenu(item) {
        if($(item).siblings('.items').css('display') == 'none'){
            $('.items').hide();
            $(item).siblings('.items').show();
        } else {
            $(item).siblings('.items').hide();

        }
      /*
             onmouseover="$(this).siblings().show();"
       onmouseout="$(this).siblings().hide();"

       */
    }
</script>
