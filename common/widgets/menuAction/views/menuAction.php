<?php
$style = ($offset < 0) ? 'style= "display: none;  margin-left: ' . $offset . 'px;";' : 'style="display: none;"';
$needConfirm = (!empty($confirm))
    ? " data-confirm='$confirm' data-method='post' "
    : "";
/*
<a href="/adminx/translation/delete?tkey=1" title="Удалить" data-confirm="Подтвердите удаление" data-method="post"><span class="glyphicon glyphicon-trash"></span></a>
 */
?>
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
