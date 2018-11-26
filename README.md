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
use Sensetivity\ChangeLog\ChangeLogListWidget;
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

echo ChangeLogListWidget::widget([
    'model' => $model,
]);
```

#### History

Controller *PostController*
```php
use Sensetivity\ChangeLog\actions\ChangeLogAction;
use app\models\Post;

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'changelog' => [
                'class' => ChangeLogAction::class,
                'modelClass' => Page::class,
            ],
        ];
    }

```
View *post/view.php*
```php
<?= Html::a('Changelog', ['changelog', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
```