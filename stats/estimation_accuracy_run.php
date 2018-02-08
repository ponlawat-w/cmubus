<?php session_start(); session_write_close(); ?>
<!doctype html>
<html ng-app="cmubus_stats">
    <head>
        <meta charset="utf-8">
        <title>ข้อมูลสถิติ→คำนวณความแม่นยำการประมาณเวลา</title>
        <link rel="stylesheet" type="text/css" href="styles.css">
        <?php
        include_once("../lib/lib.inc.php");
        include_once("../lib/app.inc.php");
        include_once("library.php");
        ?>
        <script src="../app/assets/js/angular.min.js"></script>
        <script>
            var app = angular.module("cmubus_stats", []);

            app.controller('estimationController', function($scope, $http)
            {
                $scope.calculating = false;

                $scope.finishedSession = 0;
                $scope.sessions = [];

                $scope.startCalculation = function()
                {
                    $scope.calculating = true;

                    $http.get("api/start.php?temp=" + Date.now()).then(function(response)
                    {
                        $scope.sessions = response.data;
                        $scope.finishedSession = 0;
                        $scope.calculateSession($scope.finishedSession);
                    }, function(response)
                    {
                    });
                };

                $scope.calculateSession = function(index)
                {
                    $http.get("api/session.php?id=" + $scope.sessions[index] + "&temp=" + Date.now()).then(function(response)
                    {
                        $scope.finishedSession++;

                        if($scope.finishedSession < $scope.sessions.length)
                        {
                            $scope.calculateSession($scope.finishedSession);
                        }
                    }, function(response)
                    {
                    });
                };
            });
        </script>
    </head>
    <body ng-controller="estimationController">
        <a href="index.php">← กลับ</a>
        <p ng-if="!calculating"><button ng-click="startCalculation()">คำนวณทั้งหมด</button></p>
        <p ng-if="sessions.length > 0">{{finishedSession}} / {{sessions.length}} ({{finishedSession / sessions.length * 100 | number:2}}%)</p>
    </body>
</html>