<?php

function doTest(){
    return "Test :)";
}

function uploadCert(){
    modules_loader("files");
    $dets = files_upload("hacienda", false, "p12");
    return $dets;

}

function uploadXml(){
    modules_loader("files");
    $dets = files_upload("hacienda", false, "xml");
    return $dets;

}