<?php
include_once 'imgun.php';

// загружаем изображение(указываем путь)
$imgun = new imgun('test.jpg');

/* РАБОТА С ИЗМЕНЕНИЕМ ИЗОБРАЖЕНИЯ */

// // меняем разрешение изображения (настройки: exact, portrait, landscape, auto, crop)
// $imgun->resizeImage(1500, 1200, 'auto');
//
// // сохраняем изображение(2- параметр, это качество в % от исходного)
// $imgun->saveImage('test-resize.jpg', 50);

/* СБОР ИНФОРМАЦИИ ОБ ИЗОБРАЖЕНИИ */

//Получение размера файла
$imgun->get_img_info();
