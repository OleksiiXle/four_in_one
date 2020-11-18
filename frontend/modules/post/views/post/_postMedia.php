<style>
    /* Rules for sizing the icon. */
    .material-icons.md-18 { font-size: 18px; }
    .material-icons.md-24 { font-size: 24px; }
    .material-icons.md-36 { font-size: 36px; }
    .material-icons.md-48 { font-size: 48px; }

    /* Rules for using icons as black on a light background. */
    .material-icons.md-dark { color: rgba(0, 0, 0, 0.54); }
    .material-icons.md-dark.md-inactive { color: rgba(0, 0, 0, 0.26); }

    /* Rules for using icons as white on a dark background. */
    .material-icons.md-light { color: rgba(255, 255, 255, 1); }
    .material-icons.md-light.md-inactive { color: rgba(255, 255, 255, 0.3); }
</style><table class="table table-bordered">
    <?php foreach($postMedia as $item): ?>
        <tr>
            <td><?=$item->name;?></td>
            <td><?=$item->file_name;?></td>
            <td><img src="<?=$item->urlToFile;?>" height="70 px" width="auto"></td>
            <td>
                <a href=""
                   title="Удалить"
                   onclick="deletePostMedia(<?=$item->id?>);"
                ><i class="material-icons md-48">delete</i></a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>