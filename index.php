<?php

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
        <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
        <script src="https://cdn.firebase.com/js/client/2.2.4/firebase.js"></script>
        <script src="https://cdn.firebase.com/libs/angularfire/1.0.0/angularfire.min.js"></script>
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/foundation.css" />
        <link rel="stylesheet" href="css/enlightnenment.css" />
        <script src="js/vendor/modernizr.js"></script>
        <!-- If you delete this meta tag World War Z will become a reality -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
    </head>
    <body data-ng-app="enlightenment">
        <div class="container" data-ng-controller="teaController">
            <div class="row">
                <div class="small-12 columns">
                    <h1>There are currently {{numberOfUsers}} people drinking tea</h1>
                    <h2>Share your wisdom:</h2>
                    <textarea name="message" style="height: 5rem; width: 100%;" data-ng-model="message"></textarea>
                    <h2>Messages</h2>
                </div>
            </div>
            <div class="row">
                <div class="small-12 columns">
                    <div class="panel large-3 columns" data-ng-repeat="user in teaUsersBinded" data-ng-class="{'end':$last}">
                    <blockquote>{{user.Message}}</blockquote>
                </div>
                </div>
                
             </div>
        </div>
            
            
            
            <small>Firebase data:<br>{{data}}</small>
        <script>
            (function (factory) {
                factory(jQuery);
            })(function ($) {
                var enlightenment = angular.module('enlightenment', ['firebase']);
                var firebaseRef = new Firebase("https://enlightenment.firebaseio.com/");
                var bIsSubscribed = false;
                /*Timeout time where 65535 is about 1 minute*/
                var timeoutTime = 20000;
                /*var timeoutTime = 65535 / 2;*/

                /*UID*/
                var generateUID = function () {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                };
                /*Genera UID*/
                var UID = generateUID();

                /*End UID*/



                /*Controllers*/
                enlightenment.controller('teaController', function ($scope, $interval, $firebaseObject, enFactory) {
                    /*Creiamo factory e oggetto per i bindings a tre*/
                    var factory = new enFactory();
                    var teaUsers;
                    var ID; /*Oggetto Firebase dell'utente*/
                    /*Controlla utenti ed elimina inattivi*/
                    var removeInactiveUsers = function (users, fb) {
                        $.each(users, function (key, value) {
                            if (Date.now() - value.Timestamp > timeoutTime) {
                                fb.child(key).remove();
                            }
                        });
                    };

                    /*Assegna teaUsers Firebase Object*/
                    var teaUsersFb = factory.getTeaUsers();

                    /*Aggiunge l'utente al database*/
                    if (!bIsSubscribed) {
                        ID = teaUsersFb.push({ 'UID': UID, 'Timestamp': Date.now(), 'Message': '' });
                    }

                    /*Acquisizione dati iniziale*/
                    teaUsersFb.on('value', function (snapshot) {
                        teaUsers = snapshot.val();
                        removeInactiveUsers(teaUsers, teaUsersFb);
                        $scope.$apply(function () {
                            $scope.data = teaUsers;
                            $scope.numberOfUsers = Object.keys(teaUsers).length;
                        });
                    });

                    /*Update continuo*/
                    $interval(function () {
                        teaUsersFb.on('value', function (snapshot) {
                            teaUsers = snapshot.val();
                            removeInactiveUsers(teaUsers, teaUsersFb);
                            $scope.data = teaUsers;
                            $scope.numberOfUsers = Object.keys(teaUsers).length;
                        });
                    }, 1000);

                    $interval(function () {
                        ID.set({ 'Timestamp': Date.now(), 'UID': UID, 'Message': $scope.message });
                    }, /*65536 / 2*/3000);

                    /*Binding*/
                    var syncObject = $firebaseObject(teaUsersFb);
                    syncObject.$bindTo($scope, 'teaUsersBinded');

                    /*---------------*/

                    /*Rimozione utente se abbandona pagina*/
                    var removeUser = function (event) {
                        ID.remove();
                    }

                    window.onbeforeunload = removeUser;
                    $scope.$on('$locationChangeStart', removeUser);


                });

                /*End controllers*/

                /*Factories*/
                enlightenment.factory('enFactory', function ($firebaseObject) {
                    return function () {
                        this.getTeaUsers = function () {
                            return firebaseRef.child('TeaUsers');
                        };
                    };
                });
                /*End factories*/

            }); ;
        </script>
        <script src="js/foundation.min.js"></script>
        <script>
            $(document).foundation();
        </script>
    </body>
</html>


