<span class="h1"><?= $article['title']?></span>
<article class="article">
    <p><?= nl2br($article['text']) ?></p>
    <a href="<?= $back ?>" title="Назад" class="option back"><i class="fa fa-arrow-left"></i> </a>
    <?php if (in_array('edit_post', $prives)):?>
        <a href="<?=BASE_PATH?>admin/edit/<?= $article['id_article'] ?>" title="Изменить" class="option edit" name = "<?= $article['title'] ?>"> <i class="fa fa-edit"></i> </a> 
    <?php endif;?>
    <?php if (in_array('delete_post', $prives)): ?>    
        <a href="<?=BASE_PATH?>admin/delete/<?= $article['id_article'] ?>" title="Удалить" class="option del" name = "<?= $article['title'] ?>" onclick="return confirm('Удалить статью \'<?= $article['title'] ?>\' ?');"> <i class="fa fa-times-circle-o"></i></a>
    <?php endif;?>
</article>   
