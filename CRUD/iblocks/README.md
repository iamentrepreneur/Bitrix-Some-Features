## Не забываем прописать класс в autoload bitrix

```php
CModule::AddAutoloadClasses(
    '',
    array(
        'IblockElementManager' => '/local/php_interface/classes/IblockElementManager.php',
    )
);
```