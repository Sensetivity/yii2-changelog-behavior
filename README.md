# yii2-changelog-behavior
Simple changelog behavior with diff highlight for yii2 models

## Installation

1- Install package via composer:
```
composer require sensetivity/yii2-changelog-behavior "*"
```
2- Run migrations:
```
yii migrate --migrationPath=@vendor/sensetivity/yii2-changelog-behavior/src/migrations
```

## Usage

1- Add *ChangeLogBehavior* to any model or active record:
```php
public function behaviors()
{
    return [
        ...
        [
            'class' => Sensetivity\ChangeLog\ChangeLogBehavior::class,
            'excludedAttributes' => ['updated_at', 'created_at'],
        ],
        ...
    ];
}
```
__Attention:__ Behavior watches to "safe" attributes only.
Add attributes into *excludedAttributes* if you don't want to log 
its changes.

2- Add *ChangeLogListWidget* to view:
```php
 echo Sensetivity\ChangeLog\ChangeLogListWidget::widget([
     'model' => $model,
 ])
```

3- Add custom log:
```php
$model->addCustomLog('hello world!', 'hello_type')
```

### Example

Model *Post*
```php
/**
 * @propertu int id
 * @property int created_at
 * @property int updated_at
 * @property string title
 * @property int rating
 */
class Post extends yii\db\ActiveRecord {
    
    /**
     *  @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => Sensetivity\ChangeLog\ChangeLogBehavior::class,
                'excludedAttributes' => ['created_at','updated_at'],
            ]
        ];
    }
    
}
```
View *post/view.php*
```php
use cranky4\ChangeLogBahavior\ListWidget;
use app\models\Post;

/**
 *  @var Post $model
 */
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'title',
        'rating',
        'created_at:datetime',
        'updated_at:datetime',
    ],
]);

echo Sensetivity\ChangeLog\ChangeLogListWidget::widget([
    'model' => $model,
]);

```
