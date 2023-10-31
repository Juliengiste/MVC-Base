<?php
/*******************************
 * Version : 1.0.0.0
 * Revised : jeudi 26 octobre 2017, 15:23:58 (UTC+0200)
 *******************************/

namespace Core\Models;

abstract class Metier
{
   protected $properties = [];
   protected $allowed_properties = [];
   protected $pk_name;

   public function __construct($data = null)
   {
      if (isset($data)) $this->hydrate($data);
   }

   public function getPkName()
   {
      return $this->pk_name;
   }

   public function getPropertyList()
   {
      return $this->allowed_properties;
   }

   public function getProperties()
   {
      return var_dump($this->properties);
   }

   public function __get($property)
   {
      if (!in_array($property, $this->allowed_properties)) {
         return null;
      }

      $method = 'get' . ucfirst($property);

      if (method_exists($this, $method)) {
         return $this->$method;
      }

      return isset($this->properties[$property]) ? $this->properties[$property] : null;
   }


   public function __set($property, $value)
   {
      if (!in_array($property, $this->allowed_properties)) {
         return false;
      }

      $method = 'set' . ucfirst($property);

      if (method_exists($this, $method)) {
         return $this->$method($value);
      }

      return $this->properties[$property] = $value;
   }

   public function hydrate(array $data)
   {
      foreach ($data as $property => $value) {
         $this->__set($property, $value);
      }
   }

   /**
    * Retour la liste des produits sous forme de tableau
    * @return array
    */
   public function toArray()
   {
      return $this->properties;
   }


   /**
    * Permet de décoder chaque valeur de l'objet
    * Principalement utilisé pour la génération des pdf
    */
   public function toIso88591()
   {
      foreach ($this->toArray() as $propertie => $value) {
         $a_search = array('•', '’', '–', 'œ');
         $a_replace = array('- ', "'", '-', 'oe');
         $value = str_replace($a_search, $a_replace, $value);

         $this->$propertie = utf8_decode($value);
      }
   }

   public function __isset($property)
   {
      return isset($this->properties[$property]);
   }
}