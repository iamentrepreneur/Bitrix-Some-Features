## В php init.php

```php
function getWishesCount(): int
{
    global$USER;
    $favorites = [];
    if(!$USER->IsAuthorized()) // Для неавторизованного
    {
        global $APPLICATION;
        $favorites = unserialize($_COOKIE["favorites"]);
    }
    else {
        $idUser = $USER->GetID();
        $rsUser = CUser::GetByID($idUser);
        $arUser = $rsUser->Fetch();
        $favorites = $arUser['UF_FAVORITES'];
    }

    return count($favorites);
}
```


## В футер сайта

```php
<?
$wishesCount = getWishesCount();
if($wishesCount > 0):?>
    <script>
        let wishesCount = <?php echo json_encode($wishesCount); ?>;
        let wishListCount = document.querySelector('#bookmarks .col');
        wishListCount.innerHTML = wishesCount;
    </script>
<?endif;?>
```

## В component_epilog.php

```php
if (!$USER->IsAuthorized()) {
    $arFavorites = unserialize($_COOKIE["favorites"]);
} else {
    $idUser = $USER->GetID();
    $rsUser = CUser::GetByID($idUser);
    $arUser = $rsUser->Fetch();
    $arFavorites = $arUser['UF_FAVORITES'];
}

<script>
    
</script>
?>
```

```js
document.addEventListener("DOMContentLoaded", function () {
    let favorites = arFavorites; // <?=json_encode($arFavorites);?>
    favorites.forEach(favoriteItem => {
        let favorElement = document.querySelector(`div.add-to-favorites[data-item="${favoriteItem}"]`);
        if (favorElement) {
            favorElement.classList.add("active");
        }
    });
});
```