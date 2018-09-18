<?php 
    class Point{
        private $lat;
        private $long;

        public function __construct($long, $lat){
            $this->lat = $lat;
            $this->long = $long;
        }

        public function getLong(){
            return $this->long;
        }

        public function getLat(){
            return $this->lat;
        }

        public static function getCoordinates($string){
            $pieces = explode("|", $string);
            return new Point((float)$pieces[0], (float)$pieces[1]);
        }

        public static function intersect($firstBottomLeft, $firstTopRight, $secondBottomLeft, $secondTopRight){
            // First, check if there is an overlap on the x-axis.
            $xOverLap = $firstBottomLeft->getLong() < $secondTopRight->getLong() &&
            $firstTopRight->getLong() > $secondBottomLeft->getLong();

            // Second, check if there is an overlap on the y-axis.
            $yOverLap = $firstBottomLeft->getLat() < $secondTopRight->getLat() &&
                    $firstTopRight->getLat() > $secondBottomLeft->getLat();

            // If there is an overlap in both the y and x axis, we can safely
            // conclude that there is an overlap between the two objects.
            return $xOverLap && $yOverLap;
        }

        public static function obstruct($firstBottomLeft, $firstTopRight, $secondBottomLeft, $secondTopRight){
            // First, check if there is an obstruction on the x-axis.
            $xObstruct = $firstBottomLeft->getLong() <= $secondBottomLeft->getLong() &&
            $firstTopRight->getLong() >= $secondTopRight->getLong();

            // Second, check if there is an obstruction on the y-axis.
            $yObstruct = $firstBottomLeft->getLat() <= $secondBottomLeft->getLat() &&
                    $firstTopRight->getLat() >= $secondTopRight->getLat();

            // If there is an obstruction in both the y and x axis, we can safely
            // conclude that there is an overlap between the two objects.
            return $xObstruct && $yObstruct;

        }

        //this function is used to calculare the straight line distance between the corrent point and the point in the parameter.
        public function distance($point){
            $earthRadius = 6371e3;
            $lat1InRad = deg2rad($this->lat);
            $lat2InRad = deg2rad($point ->lat);
            $diffLat = deg2rad($point->lat - $this->lat);
            $diffLon = deg2rad($point->long - $this->long);
            
            $a = sin($diffLat/2) * sin($diffLat/2) + cos($lat1InRad) * cos($lat2InRad) * sin($diffLon/2) * sin($diffLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $d = $earthRadius * $c;

            return $d;
        }
    }

?>