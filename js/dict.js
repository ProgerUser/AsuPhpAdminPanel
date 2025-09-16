$('document').ready(function () {

    // ссылки на мобильные приложения в navbar
    $("#tablet").on("click", function () {
        $("#menu").fadeToggle("fast");
    });
    // очистка полей формы
    $('#clearButton').hover(
        function () {
            $(this).css('color', 'red');
        },
        function () {
            $(this).css('color', '');
        });

    $('#clearButton').click(function () {
        $('#skills').val("");
    });

    $('#translateButton').hover(
        function () {
            $('#translateButton').switchClass("btn-secondary", "btn-primary", 0);
        },
        function () {
            $('#translateButton').switchClass("btn-primary", "btn-secondary", 0);
        });

    // переключатель направления перевода
    var direction = 'ab2ru';
    $('#translateDirection').click(function () {
        if ('ab2ru' == direction) {
            $('#sourceFlag').switchClass("flag-icon-ab", "flag-icon-ru", 0);
            $('#targetFlag').switchClass("flag-icon-ru", "flag-icon-ab", 0);
            //document.getElementById("skills").id = 'skills_ru';
            direction = 'ru2ab';
        } else {
            $('#sourceFlag').switchClass("flag-icon-ru", "flag-icon-ab", 0);
            $('#targetFlag').switchClass("flag-icon-ab", "flag-icon-ru", 0);
            //document.getElementById("skills_ru").id = 'skills';
            direction = 'ab2ru';
        }
    });

    // если фокус (курсор) в поле ввода, то отлавливаем нажатие клавиши Enter (Return)
    $('#skills').keydown(function (event) {
        if (event.keyCode == 13) {
            $('#translateButton').trigger('click');
        }
        if (event.ctrlKey && event.keyCode == 37 || event.ctrlKey && event.keyCode == 39) {
            $('#translateDirection').trigger('click');
        }
    });
    // Переключение режима кнопок алфавита (количество букв - транскрипции)
    $('.toggle').toggles({
        drag: true, // разрешить перетаскивание переключателя между позициями
        click: true, // разрешить щелчок на переключателе
        text: {
            on: '', // текст для позиции ON
            off: '' // и OFF
        },
        on: false, // включение ON в init
        width: 40, // ширина используется, если не задана в css
        height: 15, // высота, если не задана в css
    });

    // Получение уведомлений об изменениях и новое состояние:
    $('.toggle').on('toggle', function (e, active) {
        $(".totalwords").toggle();
        $(".transcription").toggle();
    });

    // console.log(direction);


    // меню lavalamp - выделить пункт меню над мышью
    $(function () {
        $(".nav").lavaLamp({
            speed: 300
        });
    });
});


//	Автодополнние
$(function () {
    $("#skills").autocomplete({
        source: 'autocomplite.php',
        delay: 300,
        minLength: 2,
        autoFocus: true,
    });
});


function myAjax() {
    document.getElementById("form").style.display = "none";
    // document.getElementById("form").style.display = "block";
    //var input = $("#skills");
    var sourceFlag = document.getElementById("sourceFlag").className;
    var targetFlag = document.getElementById("targetFlag").className;
    if (sourceFlag == "flag-icon flag-icon-ab" & targetFlag == "flag-icon flag-icon-ru") {
        var skills = document.getElementById("skills").value;
        $.ajax({
            type: 'POST',
            data: {"callFunc1": skills, "condition": "abru"},
            url: 'post.php',   // <=== CALL THE PHP FUNCTION HERE.
            success: function (response) {   // <=== VALUE RETURNED FROM FUNCTION.
                $("p").html(response);
                //document.getElementById("skills").value = '';
            },
            error: function (xhr) {
                alert("error");
            }
        });
    }
    else {
        var skills = document.getElementById("skills").value;
        $.ajax({
            type: 'POST',
            data: {"callFunc1": skills, "condition": "ruab"},
            url: 'post.php',   // <=== CALL THE PHP FUNCTION HERE.
            success: function (response) {   // <=== VALUE RETURNED FROM FUNCTION.
                $("p").html(response);
                //document.getElementById("skills").value = '';
            },
            error: function (xhr) {
                alert("error");
            }
        });
    }
}