# Создание нового модуля для Moodle

## Минимальный модуль
Попробуем разобраться в создании модулей для Moodle на примере маленького шаблонного модуля. Он будет относиться к  области локальных модулей, а потому префиксом в его название будет слово `local_` само же название `newmodule`. Получается путь для размещения его файлов `local/newmodule`.

### Регистрирование модуля
Для того, чтоб Moodle увидел наш модуль и захотел его установить, достаточно в папку добавить файл `version.php` с минимальным описанием:
```php
$plugin->component = 'local_newmodule'; 
$plugin->version = 2018061200;
$plugin->requires = 2014051200;
```
Они сообщают название нашего модуля, его текущую версию и минимальную версию Moodle необходимую для функционирования нашего модуля. Версию своего Moodle можно найти в  файле `version.php` в корне.
Теперь можно было бы приступить к его установке, но лучше сразу озаботиться более читабельным наименованием модуля. Именно оно будет выводиться в разделе **Администрирования**.

### Языковой пакет
В качестве отображаемого именни для модуля Moodle автоматически использует значение `pluginname` из языкового пакета определенного в модуле. А если он не определен, то название будет брать из `$plugin->component`. Языковой пакет представляет из себя файл php с названием совпадающим с названием нашего модуля, расположенный в папке с названием локали. Все такие папки расположены в папке `lang`.
Для нашего модуля это будет `lang/ru/local_newmodule.php`. И нам достаточно поместить единственную строку.
~~~php
$string['pluginname'] = 'newmodule';
~~~
Все другие строковые константы определяются похожим образом, а доступ к их значению можно получить с помощью функции `get_string()`.  _Пример:_ `get_string('pluginname', 'local_newmodule')`

### База данных
При установке нового модуля Moodle автоматически создает все таблицы описанные в файле `db/install.xml`. 
~~~xml
<?xml version="1.0" encoding="UTF-8" ?>
<XMLDB PATH="local/newmodule/db" VERSION="20101203" COMMENT="XMLDB file for Moodle local/newmodule"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="../../../lib/xmldb/xmldb.xsd"
>
  <TABLES>
    <TABLE NAME="newmodule" COMMENT="Default comment for newmodule, please edit me">
      <FIELDS>
        <FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" SEQUENCE="true"/>
        <FIELD NAME="course" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" SEQUENCE="false" COMMENT="Course newmodule activity belongs to"/>
        <FIELD NAME="name" TYPE="char" LENGTH="255" NOTNULL="true" SEQUENCE="false" COMMENT="name field for moodle instances"/>
        <FIELD NAME="intro" TYPE="text" NOTNULL="true" SEQUENCE="false" COMMENT="General introduction of the newmodule activity"/>
        <FIELD NAME="introformat" TYPE="int" LENGTH="4" NOTNULL="true" UNSIGNED="true" DEFAULT="0" SEQUENCE="false" COMMENT="Format of the intro field (MOODLE, HTML, MARKDOWN...)"/>
        <FIELD NAME="timecreated" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" SEQUENCE="false"/>
        <FIELD NAME="timemodified" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" DEFAULT="0" SEQUENCE="false"/>
      </FIELDS>
      <KEYS>
        <KEY NAME="primary" TYPE="primary" FIELDS="id"/>
      </KEYS>
      <INDEXES>
        <INDEX NAME="course" UNIQUE="false" FIELDS="course"/>
      </INDEXES>
    </TABLE>
  </TABLES>
</XMLDB>
~~~
Важным является указание на то к какому модулю относиться таблицы, их название, ключи и индексы, если они нужны. Сами поля описываются своим названием, типом, обязательностью заполнения, значениями по-умолчанию. 

### Установка
Вот и все! Теперь можно идти в браузере в `Настройка -> Администрирование -> Уведомления` или `admin/index.php` и устанавливать модуль.


### Стили
Для добавления в в модуль стилий достаточно лишь прописать их в файле `styles.css`. 

> Но нужно быть придельно бдительным и не экономить на точности указания селектора элемента, к которому должен применяться стиль. Так как все файлы `styles.css` склеиваются в Moodle  в один, который потом загружается уже на всех страницах. А потому лень чревата тем что можно ненароком перекрасить весь Moodle.

### Шаблоны
Одним из самых простых способов добавить `html` разметку для отображения данных это использовать шаблоны. Все они размещаются в папке `templetes` и носят произвольное название с расширением `.mustache`. Подробнее об таком типе шаблонизации можно прочитать [здесь](https://mustache.github.io/mustache.5.html) или [здесь](https://docs.moodle.org/dev/Templates).
Связывание шаблона и модуля происходит через объект класса `renderer`. С одной стороны это дополнительная головная боль, но с другой стороны в шаблон легче вносить правки, а функции определенного по всем правилам `renderer` можно потом переопределять в разных темах по своему.
Большинство классов, придлежащих модулям Moodle умеет находить автоматически, что освобождает от необходимости подключать их вручную, но одновременно накладывает ограничение на названия файлов и их расположение. Наш класс `renderer` будет располагаться `classes/output/renderer.php`.
```php
namespace local_newmodule\output;
use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    public function render_main(main $main) {
        return $this->render_from_template('local_newmodule/main', $main->export_for_template($this));
    }
}
```
Во избежание конфликта имен мы объявляем для всех классов нашего модуля свое собственное пространство `local_newmodule\output`, которое совпадает с путем до этого файла (так и Moodle и нам позжнее проще ориетироваться). Большинство методов наш класс `renderer` наследует от своего родителя `plugin_renderer_base`. Мы лишь определяем функцию `render_main(main $main)` --- это своего рода уловка метапрограммирования. В классе `plugin_renderer_base` функция `renderer` определена так, что может искать для вызова функции с названием `renderer_<название класса аргумента>` т.е. для нашей функции `plugin_renderer_base` нужно, чтоб аргумент был экземпляром классса `main`.
Само определение класса должно располагаться в файле `classes\output\main.php` и содержать функцию, которая будет возвращать данные для отрисовки в шаблоне. Обычно таакую функцию называют `export_for_template()` и возвращает она либо массив либо объект, которые будут за кадром преобразованны в `json`.
```php
namespace local_newmodule\output;

use renderable;
use renderer_base;
use templatable;

class main implements renderable, templatable {

    public function export_for_template(renderer_base $output) {
        return [
            'message' => "Hello world"
        ];
    }
}
```
Наш класс `main.php` живает в том же пространстве имен `local_newmodule\output` и возращает простой ассоциироваанный массив с единственным элементом.
Чтоб увидить результат взаимодействия всех классов и шаблона в файл `index.php` мы дабавим следующие строки.
```php
$renderable = new \local_newmodule\output\main();
$renderer = $PAGE->get_renderer('local_newmodule');

echo $renderer->render($renderable);
```
Первая строка создает для нас экземпляр класса `main`. Ему для обработки могли бы быть переданны любые данные, например `id` пользователя и нас внутри бы ждали данные по его успеваемости запрошенные из базы. Во второй строке мы запрашиваем экземпляр нашего класса `renderer`. Именно он знает о шаблонах и о том как нужно отрисовывать нашу информацию. А последней строкой мы запускаем процесс заполнения наших шаблонов данными. Заметьте запускаем мы не саму функцию `render_main`, а `render`. В данном случае это лишь демонстрация возможности полиморфизма, когда имея коллекцию заполненную объектами разных классов (`quiz`, `assign` и т.д.) мы можем натравливать на них одну лишь функцию `render`, а она сама уже будет делигировать обязанности на `render_quiz`, `render_assign` и т.д.

### Доступы
Основой для разделение прав являеются полномочия (capability), которые выдаются в зависимости от роли. Определить права для своего модуля можно в файле `db/access.php`. Он состоит из одной лишь переменной `$capabilities`, которая представляет из себя массив со всеми правами.
```php
$capabilities = array(
    'local/newmodule:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'guest' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/search:query'
    )
);
```
Название права начинается с указания области модулей и названия модуля, а потом уже самоназваине права (`allview`,`submit` и т.д.). Для каждого права определяется его категория (`read`, `write`), конкекст (дисциплина, блок, система, пользователь), а так же те роли для которых по-умолчанию право дано или нет. Возможно так же скопировать распределения выдачи прав от друго права.
| Название | Описание | Уровень |
| ---- | ---- | ----- | 
| CONTEXT_SYSTEM | контекст системы | 10 |
| CONTEXT_USER | контекст пользователя | 30 |
| CONTEXT_COURSECAT	| контекст категории | 40 |
| CONTEXT_COURSE | контекст курса | 50 |
| CONTEXT_MODULE | контекст модуля дисуиплины | 70 |
| CONTEXT_BLOCK	| контекст блока |	80 |
Кроме того каждое право может быть помечено риском, которым чревато его владение. Такая информация может быть весьма полезной, когда администрирование Moodle выполняется другими людьми.
| Название опастности | Описание |
| ------ | ------ |
| RISK_SPAM | пользователь може рассылать назойливый контект другим пользователям |
| RISK_PERSONAL | доступ к персональным данным |
| RISK_XSS | возможна отправка контента не очищенного от уязвиостей |
| RISK_CONFIG | пользователь может изменить конфигурации всей системы |
| RISK_MANAGETRUST | изменение прав для других пользователей |
| RISK_DATALOSS | возможна потеря больших объемов информации |
Для того, чтоб в ходе выполнения модуля проверить права пользователя можно воспользоваться функциями `require_capability('<название права>', $context)` и `has_capability('<название права>', $context)`. Разница этих функций в том, что первая прервет доступ к модулю и выведет сообщение об отсуттсвии права. А вторая возвращает нам `true` или `false`, и уже нам решать как с этим поступить. Но каждый запрос о правах требует указания контекста,  котором оно должно быть выдано. Для получени контекста можно воспользоваться семейством функций:
```php
$systemcontext = context_system::instance();
$usercontext = context_user::instance($user->id);
$categorycontext = context_coursecat::instance($category->id);
$coursecontext = context_course::instance($course->id);
$contextmodule = context_module::instance($cm->id);
```
