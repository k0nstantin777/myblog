<span class="h1">Список статей</span>
<article>
<ul>
    <? foreach($articles as $article): ?>
    <li>
        <span class="title">
            <a href="<?=BASE_PATH?>post/<?= $article['id_article'] ?>" id="name" title="Читать" name ="<?= $article['title'] ?>"><?= $article['title'] ?></a>
            <span class = "option"> ( <?= date('H:i d-m-Y', strtotime($article['dt'])) ?> ) </span>
        </span>
        <? if ($login === true):?>
            <a href="<?=BASE_PATH?>edit/<?= $article['id_article'] ?>" class="option edit" title="Изменить" name = "<?= $article['title'] ?>"><i class="fa fa-edit"></i></a>
            <a href="<?=BASE_PATH?>delete/<?= $article['id_article'] ?>"class="option del" title="Удалить" name = "<?= $article['title'] ?>" onclick="return confirm('Удалить статью: \'<?= $article['title'] ?>\' ?');"> <i class="fa fa-times-circle-o"></i></a>
        <? endif;?>
    </li>
    <? endforeach; ?> 
</ul>
    <? if ($login === true):?>
    <a class="add" href="<?=BASE_PATH?>add">Добавить статью</a>
    <? endif;?>
</article>
<p><i><?=$msg?></i></p>
   