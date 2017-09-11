/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
'use strict';
angular.module('status', ['ngStorage'])
    .controller('statusController', ['$scope', '$interval', '$sce', '$timeout', '$localStorage', '$rootScope', '$http', function ($scope, $interval, $sce, $timeout, $localStorage, $rootScope, $http) {
        $scope.isConsole = true;
        $scope.isShowCleanUpBox = false;
        $scope.error = false;
        $scope.rollbackStarted = false;
        $scope.nextButton = false;

        $scope.toggleConsole = function () {
            $scope.isConsole = $scope.isConsole === false;
        };
        $scope.rollback = function () {
            $http.post('index.php/rollback');
            $scope.error = true;
            $scope.rollbackStarted = true;
        };
        $scope.goToSuccessPage = function () {
            window.location.href = '../setup/index.php#/updater-success';
        };

        $interval(
            function () {
                $http.post('index.php/status')
                    .success(function (result) {
                        if (result['complete']) {
                            $localStorage.rollbackStarted = $scope.rollbackStarted;
                            if ($scope.rollbackStarted === true) {
                                $scope.nextButton = true;
                            } else {
                                $scope.goToSuccessPage();
                            }
                        }
                        if (result.statusMessage) {
                            $('#console').html(result.statusMessage);
                        }
                        var statusText = "";
                        if (result.isUpdateInProgress) {
                            statusText = "Update application is running";
                        } else if (result.pending) {
                            statusText = "Update pending";
                        } else {
                            statusText = "Update application is not running";
                        }
                        $('#status').html(statusText);
                        if (result['error'] || $scope.error) {
                            $scope.error = true;
                        }
                    })
                    .error(function (result) {
                        $scope.error = true;
                        $scope.rollbackStarted = false;
                    });
            },
            3000
        );
        $interval(
            function () {
                $http.post('../setup/index.php/session/prolong')
                    .success(function (result) {
                    })
                    .error(function (result) {
                    });
            },
            120000
        );
    }]);
