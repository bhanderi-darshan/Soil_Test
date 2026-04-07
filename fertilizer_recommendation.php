<?php

function recommendFertilizer($n, $p, $k)
{
 $result = [];

 if ($n < 40) {
  $result[] = "Apply Urea (Nitrogen Low)";
 }

 if ($p < 20) {
  $result[] = "Apply DAP (Phosphorus Low)";
 }

 if ($k < 120) {
  $result[] = "Apply MOP (Potassium Low)";
 }

 if (empty($result)) {
  $result[] = "Soil nutrients are sufficient";
 }

 return $result;
}
?>