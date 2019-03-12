<?php

namespace Typographus\Profiles;
/**
 * Русский профиль для Типографа
 */
class Russian extends TypographusProfile
{
    protected $abbr = 'ООО|ОАО|ЗАО|ЧП|ИП|НПФ|НИИ|ООО\p{Zs}ТПК';
    protected $prepos = 'а|в|во|вне|и|или|к|о|с|у|о|со|об|обо|от|ото|то|на|не|ни|но|из|изо|за|уж|на|по|под|подо|пред|предо|про|над|надо|как|без|безо|что|да|для|до|там|ещё|их|или|ко|меж|между|перед|передо|около|через|сквозь|для|при|я';
    protected $metrics = 'мм|см|м|км|г|кг|б|кб|мб|гб|dpi|px';
    protected $shortages = 'г|гр|тов|пос|c|ул|д|пер|м|зам|см';
    protected $money = 'руб\.|долл\.|евро|у\.е\.';
    protected $countables = 'млн|тыс';

    protected $rules_strict;
    protected $rules_symbols;
    protected $rules_quotes;
    protected $rules_braces;
    protected $rules_main;

    function __construct()
    {
        $this->rules_strict = array(
            // Много пробелов или табуляций -> один пробел
            '~( |\t)+~u' => ' ',
            // Запятые после «а» и «но». Если уже есть — не ставим.
            '~([^,])\s(а|но)\s~u' => '$1, $2 ',
        );


        $this->rules_symbols = array(
            //Лишние знаки.
            //TODO: сделать красиво
            '~([^!])!!([^!])~u' => '$1!$2',
            '~([^?])\?\?([^?])~u' => '$1?$2',
            '~(\p{L});;(\p{Zs})~u' => '$1;$2',
            '~(\p{L})\.\.(\p{Zs})~u' => '$1.$2',
            '~(\p{L}),,(\p{Zs})~u' => '$1,$2',
            '~(\p{L})::(\p{Zs})~u' => '$1:$2',

            '~(!!!)!+~u' => '$1',
            '~(\?\?\?)\?+~u' => '$1',
            '~(;;;);+~u' => '$1',
            '~(\.\.\.)\.+~u' => '$1',
            '~(,,,),+~u' => '$1',
            '~(:::):+\s~u' => '$1',

            //Занятная комбинация
            '~!\?~u' => '?!',

            // Знаки (c), (r), (tm)
            '~\((c|с)\)~iu' => '©',
            '~\(r\)~iu' => '<sup><small>®</small></sup>',
            '~\(tm\)~iu' => '<sup>™</sup>',

            // От 2 до 5 знака точки подряд - на знак многоточия (больше - мб авторской задумкой).
            "~\.{2,5}~u" => '…',

            // Дроби
            // TODO: найти замену \b
            '~\b1/2\b~u' => '½',
            '~\b1/3\b~u' => '⅓',
            '~\b1/4\b~u' => '¼',
            '~\b3/4\b~u' => '¾',

            // LО'Лайт, O'Reilly
            "~([a-zA-Z])'([а-яА-Яa-zA-Z])~iu" => '$1’$2',

            "~'~u" => '&#39;', //str_replace?

            // Размеры 10x10, правильный знак + убираем лишние пробелы
            '~(\p{Nd}+)\p{Zs}{0,}?[x|X|х|Х|*]\p{Zs}{0,}(\p{Nd}+)~u' => '$1×$2',

            //+-
            '~([^\+]|^)\+-~u' => '$1±',

            //Стрелки
            '~([^-]|^)->~u' => '$1→',
            '~<-([^-]|$)~u' => '←$1',
        );


        $this->rules_quotes = array(
            // Разносим неправильные кавычки
            '~([^"]\p{L}+)"(\p{L}+)"~u' => '$1 "$2"',
            '~"(\p{L}+)"(\p{L}+)~u' => '"$1" $2',

            // Превращаем кавычки в ёлочки.
            '~(\P{L})?"([^"]*)"(\P{L})?~u' => '$1«$2»$3',
        );

        $this->rules_braces = array(
            // Оторвать скобку от слова
            '~(\p{L})\(~u' => '$1 (',
            //Слепляем скобки со словами
            '~\( ~su' => '(',
            '~ \)~su' => ')',
        );

        $this->rules_main = array(
            // Конфликт с «газо- и электросварка»
            // Оторвать тире от слова
            //'~(\p{L})- ~' => '$1 - ',

            //Знаки с предшествующим пробелом… нехорошо!
            '~(\p{L}|>|\p{Nd}) +([?!:,;]|…)~' => '$1$2',
            '~([?!:,;])(\p{L}|<)~' => '$1 $2',
            //Для точки отдельно
            '~(\p{L})\p{Zs}(?:\.)(\p{Zs}|$)~u' => '$1.$2',
            // Но перед кавычками пробелов не ставим
            '~([?!:,;\.])\p{Zs}(»)~' => '$1$2',

            //Неразрывные названия организаций и абревиатуры форм собственности
            // ~ почему не один &nbsp;?
            // ! названия организаций тоже могут содержать пробел !
            '~(' . $this->abbr . ')\p{Zs}+(«[^»]*»)~u' => '<span style="white-space:nowrap">$1 $2</span>',

            //Нельзя отрывать сокращение от относящегося к нему слова.
            //Например: тов. Сталин, г. Воронеж
            //Ставит пробел, если его нет.
            '~(^|[^a-zA-Zа-яА-Я])(' . $this->shortages . ')\.\s?([А-Я0-9]+)~su' => '$1$2.&nbsp;$3',

            //Не отделять стр., с. и т.д. от номера.
            '~(стр|с|табл|рис|илл)\.\p{Zs}*(\p{Nd}+)~siu' => '$1.&nbsp;$2',

            //Не разделять 2007 г., ставить пробел, если его нет. Ставит точку, если её нет.
            '~({Nd}+)\p{Zs}*([гГ])\.\s~su' => '$1&nbsp;$2. ',

            //Неразрывный пробел между цифрой и единицей измерения
            '~(\p{Nd}+)\s*(' . $this->metrics . ')~su' => '$1&nbsp;$2',

            //Сантиметр и другие ед. измерения в квадрате, кубе и т.д.
            '~(\p{Zs}' . $this->metrics . ')(\p{Nd}+)~u' => '$1<sup>$2</sup>',

            // Знак дефиса или два знака дефиса подряд — на знак длинного тире.
            // + Нельзя разрывать строку перед тире, например: Знание&nbsp;— сила, Курить&nbsp;— здоровью вредить.
            '~\p{Zs}+(?:--?|—)(?=\p{Zs})~u' => '&nbsp;—',
            '~^(?:--?|—)(?=\p{Zs})~u' => '—',

            //Прямая речь
            '~(?:^|\s+)(?:--?|—)(?=\p{Zs})~u' => "\n&nbsp;—",

            // Знак дефиса, ограниченный с обоих сторон цифрами — на знак короткого тире.
            '~(?<=\p{Nd})-(?=\p{Nd})~u' => '–',

            // Знак дефиса, ограниченный с обоих сторон пробелами — на знак длинного тире.
            '~(\s)(&ndash|–)(\s)~u' => '&nbsp;&mdash; ',

            // Знак дефиса, идущий после тэга и справа пробел — на знак длинного тире.
            '~(?<=>)(&ndash|–|-)(\s)~u' => '&mdash; ',

            // Нельзя оставлять в конце строки предлоги и союзы
            '~(?<=\p{Zs}|^|\P{L})(' . $this->prepos . ')(\s+)~iu' => '$1&nbsp;',

            // Нельзя отрывать частицы бы, ли, же от предшествующего слова, например: как бы, вряд ли, так же.
            '~(?<=\P{Zs})(\p{Zs}+)(ж|бы|б|же|ли|ль|либо|или)(?=<.*?>*[\p{Zs})!?.])~iu' => '&nbsp;$2',

            // Неразрывный пробел после инициалов.
            '~([А-ЯA-Z]\.)\s?([А-ЯA-Z]\.)\p{Zs}?([А-Яа-яA-Za-z]+)~su' => '$1$2&nbsp;$3',

            // Сокращения сумм не отделяются от чисел.
            '~(\p{Nd}+)\p{Zs}?(' . $this->countables . ')~su' => '$1&nbsp;$2',

            //«уе» в денежных суммах
            '~(\p{Nd}+|' . $this->countables . ')\p{Zs}?уе~su' => '$1&nbsp;у.е.',

            // Денежные суммы, расставляя пробелы в нужных местах.
            '~(\p{Nd}+|' . $this->countables . ')\p{Zs}?(' . $this->money . ')~su' => '$1&nbsp;$2',

            // Неразрывные пробелы в кавычках
            //"/($sym[lquote]\S*)(\s+)(\S*$sym[rquote])/U" => '$1'.$sym["nbsp"].'$3',

            //Телефоны
            '~(?:тел\.?/?факс:?\s?\((\d+)\))~i' => 'тел./факс:&nbsp($1)',

            '~тел[:\.] ?(\p{Nd}+)~su' => '<span style="white-space:nowrap">тел: $1</span>',

            //Номер версии программы пишем неразрывно с буковкой v.
            '~([vв]\.) ?(\p{Nd})~iu' => '$1&nbsp;$2',
            '~(\p{L}) ([vв]\.)~iu' => '$1&nbsp;$2',

            //% не отделяется от числа
            '~(\p{Nd}+)\p{Zs}+%~u' => '$1%',

            //IP-адреса рвать нехорошо
            '~(1\p{Nd}{0,2}|2(\p{Nd}|[0-5]\p{Nd}+)?)\.(0|1\p{Nd}{0,2}|2(\p{Nd}|[0-5]\p{Nd})?)\.(0|1\p{Nd}{0,2}|2(\p{Nd}|[0-5]\p{Nd})?)\.(0|1\p{Nd}{0,2}|2(\p{Nd}|[0-5]\p{Nd})?)~' =>
                '<span style="white-space:nowrap">$0</span>',
        );
    }

    function process($str)
    {

        $str = $this->applyRules($this->rules_quotes, $str); //<------- DON'T WORK PROP!!!
        $str = $this->applyRules($this->rules_strict, $str); // Сначала применим строгие правила: пробелы, запятые
        $str = $this->quotes($str); // правильно расставим кавычки
        $str = $this->applyRules($this->rules_main, $str);
        $str = $this->applyRules($this->rules_symbols, $str);
        $str = $this->applyRules($this->rules_braces, $str);

        return $str;
    }

    private function applyRules($rules, $str)
    {
        //TODO: можно лучше?
        return preg_replace(array_keys($rules), array_values($rules), $str);
    }

    function quotes($text)
    {
        $quot11 = '«';
        $quot12 = '»';
        $quot21 = '„';
        $quot22 = '“';

        $quotes = array('&quot;', '&laquo;', '&raquo;', '«', '»', '&#171;', '&#187;', '&#147;', '&#132;', '&#8222;', '&#8220;', '„', '“', '”', '‘', '’');
        $text = str_replace($quotes, '"', $text); // Единый тип кавычек
        $text = str_replace('""', '"', $text);

        $text = preg_replace('~"(\P{L})~u', '»$1', $text); // Взято из старой реализации
        $text = preg_replace('~(\P{L})"~u', '$1«', $text); // Взято из старой реализации

//      $text=preg_replace('/([^=]|\A)""(\.{2,4}[а-яА-Я\w\-]+|[а-яА-Я\w\-]+)/', '$1<typo:quot1>"$2', $text); // Двойных кавычек уже нет
        $text = preg_replace('/([^=]|\A)"(\.{2,4}[\p{L}\p{M}]+|[\p{L}\p{M}\-]+)/', '$1<typo:quot1>$2', $text);
//      $text=preg_replace('/([а-яА-Я\w\.\-]+)""([\n\.\?\!, \)][^>]{0,1})/', '$1"</typo:quot1>$2', $text); // Двойных кавычек уже нет
        $text = preg_replace('/([\p{L}\p{M}\.\-]+)"([\n\.\?\!, \)][^>]{0,1})/', '$1</typo:quot1>$2', $text);
        $text = preg_replace('/(<\/typo:quot1>[\.\?\!]{1,3})"([\n\.\?\!, \)][^>]{0,1})/', '$1</typo:quot1>$2', $text);
        $text = preg_replace('/(<typo:quot1>[\p{L}\p{M}\.\- \n]*?)<typo:quot1>(.+?)<\/typo:quot1>/', '$1<typo:quot2>$2</typo:quot2>', $text);
        $text = preg_replace('/(<\/typo:quot2>.+?)<typo:quot1>(.+?)<\/typo:quot1>/', '$1<typo:quot2>$2</typo:quot2>', $text);
        $text = preg_replace('/(<typo:quot2>.+?<\/typo:quot2>)\.(.+?<typo:quot1>)/', '$1</typo:quot1>.$2', $text);
        $text = preg_replace('/(<typo:quot2>.+?<\/typo:quot2>)\.(?!<\/typo:quot1>)/', '$1</typo:quot1>.$2$3$4', $text);
//      $text=preg_replace('/""/', '</typo:quot2></typo:quot1>', $text); // Двойных кавычек уже нет
        $text = preg_replace('/(?<=<typo:quot1>)(.+?)<typo:quot2>(.+?)(?!<\/typo:quot2>)/', '$1<typo:quot2>$2', $text);
//      $text=preg_replace('/"/', '<typo:quot1>', $text); // Непонятный хак
//      $text=preg_replace('/(<[^>]+)<\/typo:quot\d>/', '$1"', $text); // Еще более непонятный хак

        $text = str_replace('<typo:quot1>', $quot11, $text);
        $text = str_replace('</typo:quot1>', $quot12, $text);
        $text = str_replace('<typo:quot2>', $quot21, $text);
        $text = str_replace('</typo:quot2>', $quot22, $text);

        return $text;
    }
}
