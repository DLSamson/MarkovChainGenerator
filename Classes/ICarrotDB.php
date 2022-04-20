<?php

interface ICarrotDB {
    public function getData() : CarrotChain;
    public function setData() : void;
    public function checkData() : CarrotChain;

}