<?php
Class imgun
{
      private $image;
      private $width;
      private $height;
      private $imageResized;

      function __construct($fileName)
      {
          // Открываем файл
          $this->image = $this->openImage($fileName);
          $this->filename = $fileName;

          //Получаем ширину и высоту
          $this->width  = imagesx($this->image);
          $this->height = imagesy($this->image);
      }

      //Получаем расширение
      private function openImage($file)
      {
          $extension = strtolower(strrchr($file, '.'));
          switch($extension)
          {
              case '.jpg':
              case '.jpeg':
                  $img = @imagecreatefromjpeg($file);
                  break;
              case '.gif':
                  $img = @imagecreatefromgif($file);
                  break;
              case '.png':
                  $img = @imagecreatefrompng($file);
                  break;
              default:
                  $img = false;
                  break;
          }
          return $img;
      }


      //Получить информацию об изображении
      public function get_img_info(){
        $tmp_arr = getimagesize($this->filename);

        $arrToAnswer = [
          'width'=>$tmp_arr[0],
          'height'=>$tmp_arr[1],
          'mime'=>$tmp_arr['mime'],
          'size'=>filesize($this->filename),
        ];

        return $arrToAnswer;
      }

      //Меняем размер изображения
      public function resizeImage($newWidth, $newHeight, $option="auto")
        {
            $optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));
            $optimalWidth  = $optionArray['optimalWidth'];
            $optimalHeight = $optionArray['optimalHeight'];
            $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
            imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);
            if ($option == 'crop') { $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight); }
        }

        //Получаем размеры
        private function getDimensions($newWidth, $newHeight, $option)
          {
             switch ($option)
              {
                  case 'exact':
                      $optimalWidth = $newWidth;
                      $optimalHeight= $newHeight;
                      break;
                  case 'portrait':
                      $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                      $optimalHeight= $newHeight;
                      break;
                  case 'landscape':
                      $optimalWidth = $newWidth;
                      $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                      break;
                  case 'auto':
                      $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                      $optimalWidth = $optionArray['optimalWidth'];
                      $optimalHeight = $optionArray['optimalHeight'];
                      break;
                  case 'crop':
                      $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                      $optimalWidth = $optionArray['optimalWidth'];
                      $optimalHeight = $optionArray['optimalHeight'];
                      break;
              }
              return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
          }

          //Получить размеры при фиксированной высоте
          private function getSizeByFixedHeight($newHeight)
          {
              $ratio = $this->width / $this->height;
              $newWidth = $newHeight * $ratio;
              return $newWidth;
          }

          //Получить размеры при фиксированной ширине
          private function getSizeByFixedWidth($newWidth)
          {
              $ratio = $this->height / $this->width;
              $newHeight = $newWidth * $ratio;
              return $newHeight;
          }

          //Получить размеры автоматически
          private function getSizeByAuto($newWidth, $newHeight)
          {
              //Если у изображения больше ширина
              if ($this->height < $this->width){
                  $optimalWidth = $newWidth;
                  $optimalHeight= $this->getSizeByFixedWidth($newWidth);
              }
              //Если у изображения больше высота
              elseif ($this->height > $this->width)
              {
                  $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                  $optimalHeight= $newHeight;
              }
              else
              //Иначе - делаем изображение с равными сторонами
              {
                  if ($newHeight < $newWidth) {
                      $optimalWidth = $newWidth;
                      $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                  } else if ($newHeight > $newWidth) {
                      $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                      $optimalHeight= $newHeight;
                  } else {
                      $optimalWidth = $newWidth;
                      $optimalHeight= $newHeight;
                  }
              }

              return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
          }

          private function getOptimalCrop($newWidth, $newHeight)
          {

              $heightRatio = $this->height / $newHeight;
              $widthRatio  = $this->width /  $newWidth;

              if ($heightRatio < $widthRatio) {
                  $optimalRatio = $heightRatio;
              } else {
                  $optimalRatio = $widthRatio;
              }

              $optimalHeight = $this->height / $optimalRatio;
              $optimalWidth  = $this->width  / $optimalRatio;

              return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
          }

          //Обрезка изображения
          private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
          {
              //Вычисляем центр изображения
              $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
              $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

              $crop = $this->imageResized;
              //imagedestroy($this->imageResized);

              //Обрезаем от центра до заданного размера
              $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
              imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
          }

          //Сохраняем изображение
          public function saveImage($savePath, $imageQuality="100")
            {
                //Получаем расширение
                $extension = strrchr($savePath, '.');
                $extension = strtolower($extension);

                switch($extension)
                {
                    case '.jpg':
                    case '.jpeg':
                        if (imagetypes() & IMG_JPG) {
                            imagejpeg($this->imageResized, $savePath, $imageQuality);
                        }
                        break;

                    case '.gif':
                        if (imagetypes() & IMG_GIF) {
                            imagegif($this->imageResized, $savePath);
                        }
                        break;

                    case '.png':
                        //Подгоняем качество
                        $scaleQuality = round(($imageQuality/100) * 9);

                        $invertScaleQuality = 9 - $scaleQuality;

                        if (imagetypes() & IMG_PNG) {
                            imagepng($this->imageResized, $savePath, $invertScaleQuality);
                        }
                        break;


                    default:
                        // Пока ничего не делаем
                        break;
                }

                imagedestroy($this->imageResized);
            }

}
