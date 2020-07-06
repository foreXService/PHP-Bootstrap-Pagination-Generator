# PHP BOOTSTRAP PAGINATION
Create Bootstrap pagination element in php code .

## Create 

```php
$pagination = (new Bootstrap\Pagination())
    ->setPage(5)
    ->setPages(20);
```
or

```php
$pagination = new Bootstrap\Pagination([
    'page' => 5,
    'pages' => 20
]);
```

## Render

```php
(new Bootstrap\Pagination())
    ->setPage(5)
    ->setPages(20)
    ->echoPagination();
```
or

```php
new Bootstrap\Pagination([
    'page' => 5,
    'pages' => 20,
    'show' => true
]);
```
or
```php
echo $pagination;
```
or
```php
$pagination->echoPagination();
```

result
![alt text](/img/example1.png)