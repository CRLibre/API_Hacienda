/****************************************************************************
/*!
 * Cala Framework: To make your life simpler
 *
 * Copyright (c) 2018 CRLibre
 * License: MIT - CalaApi
 * Include this AT THE BOTTOM of your pages, that is all you need to do.
 *
 *           | |      
 *   ___ __ _| | __ _ 
 *  / __/ _` | |/ _` |
 * | (_| (_| | | (_| |
 *  \___\__,_|_|\__,_|
 *
 *****************************************************************************/                   
// Version

/*******************************/
/*            CONS             */
/*******************************/

// Users
var ERROR_NO_VALID_USER = "-300";
var ERROR_USER_WRONG_LOGIN_INFO = "-301";
var ERROR_USER_NO_VALID_SESSION = "-302"; // Not in use I think
var ERROR_USER_ACCESS_DENIED = "-303";
var ERROR_USER_EXISTS = "-304";
var ERROR_USERS_NO_TOKEN = "-305";

//Database
var ERROR_DB_NO_RESULTS_FOUND = "-200";
var ERROR_BAD_REQUEST = "-1";

//Others
var ERROR_ERROR = "-2";
var ERROR_MODULE_UNDEFINED = "-3";
var ERROR_MODULE_NOT_FOUND = "-4";
var ERROR_FUNCTION_NOT_FOUND = "-5";
var SUCCESS_ALL_GOOD = "1";


/*******************************/
/*          Settings           */
/*******************************/
//This is the url where your api.php is located
var calaApi_url = "";

//Default frontpage or index
var calaApi_front = "index.html";

//Var to store user data
var calaApi_user = "";

//If you want to debug or not   
var calaApi_debugMode = true;
/***************************************************/
/* Register a new user                             */
/* Requiere:                                       */
/* fullName, userName, email, about, country, pwd  */
/***************************************************/
function calaApi_registerUser(userData, success, error){
    
    var req = {
        w: "users",
        r: "users_register",
        fullName: userData.fullName,
        userName: userData.userName,
        email: userData.userEmail,
        pwd: userData.pwd,
        about: userData.about,
        country: userData.userCountry,
        inst: userData.inst
    };
    
    calaApi_postRequest(req, 
        function(d){
            calaApi_setLocalStorage('userName', d.resp.userName);   
            calaApi_setLocalStorage('sessionKey', d.resp.sessionKey); 
            success(d);
        }, function(d){
            error(d);
        });
}

/*********************************************/
/* Function to check login users             */
/* Req success, error                        */
/*********************************************/
function calaApi_checkLogin(success, error, timeout){
    var req = {
        w: "users",
        r: "users_get_my_details"
    };
    
    calaApi_postRequest(req, 
    function(data){
        if(data.resp != ERROR_USER_WRONG_LOGIN_INFO && data.resp != ERROR_USER_ACCESS_DENIED){
            if(success != null){
                calaApi_debug("It seems we are logged in as " + data.resp.userName);
                calaApi_user = data.resp.userName;
                success(data);
            }
        }else{
            if(error != null){
                error(data);
            }
        }
    },
    function(){
        calaApi_debug("Exec error func");
        if(error != null){
            error();
        }
    },
    function(){
        calaApi_debug("Exec callback func");
        if(timeout != null){
            timeout();
        }
    });
}

/*********************************************/
/* Function to create users                   */
/* Req userData, func success, func error    */
/*********************************************/
function calaApiApi_login(userData, success, error){
 var req = {
            w: "users",
            r: "users_log_me_in",
            userName: userData.userName,
            pwd: userData.pwd
        };
        
 calaApi_postRequest(req, 
    function(data){
        if(data.resp != ERROR_USER_WRONG_LOGIN_INFO){
            calaApi_setLocalStorage('userName', data.resp.userName);   
            calaApi_setLocalStorage('sessionKey', data.resp.sessionKey); 
            if(success != null){
                success(data);
            }
        }else{
            if(error != null){
                error(data);
            }
        }
    },
    function(data){
        if(error != null){
            error(data);
        }
    });
}

/*********************************************/
/* Function to recover pss                   */
/* Req userName                              */
/*********************************************/
function calaApiApi_recoverPwd(userName, success, error){
 var req = {
            w: "users",
            r: "users_recover_pwd",
            userName: userName,
        };
        
 calaApi_postRequest(req, 
    function(data){
        if(data.resp == SUCCESS_ALL_GOOD){
            if(success != null){
                success(data);
            }
        }else{
            if(error != null){
                error(data);
            }
        }
    },
    function(data){
        if(error != null){
            error(data);
        }
    });
}

/*********************************************/
/* Function set local storage                */
/* Req key, value                            */
/*********************************************/
function calaApi_setLocalStorage(k, v){
    calaApi_debug("Saving in storage K: " + k + " V: " + v);
    localStorage.setItem(k, v);
}

/*********************************************/
/* Function set local storage                */
/* Req key                                   */
/* Return value                              */
/*********************************************/
function calaApi_getLocalStorage(k){
    var v = localStorage.getItem(k);
    calaApi_debug("Getting from storage K: " + k + " GOT: " + v);
    return v;
}

/*********************************************/
/* Function to make post reqs                */
/* Req request data, func success, func error*/
/*********************************************/
function calaApi_postRequest(req, success, error, timeout = 800, times = 0){
    calaApi_debug("Making a post request to " + calaApi_url);
    /*generate the form*/
    var _data = new FormData();
    
    for (var key in req) {
        var value = req[key];
        calaApi_debug("Adding " + key + " -> " + value);
        _data.append(key, value);
    }   

    _data.append("iam", calaApi_getLocalStorage('userName'));
    _data.append("sessionKey", calaApi_getLocalStorage('sessionKey'));

    var oReq = new XMLHttpRequest();
    oReq.open("POST", calaApi_url, true);
    
    oReq.timeout = timeout;
    
    oReq.onload = function(oEvent) {
        if(oReq.status == 200) {
            var r = oReq.responseText;
            console.log(r);
            r = JSON.parse(r);
            success(r);
            calaApi_debug("Done!");
        }else{
            var r = oReq.responseText;
            calaApi_debug("There was an error");
            error(r);
        }
    };
    
    oReq.ontimeout = function(e){
        times++;
        if(times < 3){
            calaApi_postRequest(req, success, error, timeout, (times++));
            calaApi_debug("Timeout " + times + ", lets try again");
        }else{
            calaApi_doSomethingAfter(function(){
                calaApi_debug("Timeout does not work, retrying in a sec");
                calaApi_postRequest(req, success, error, timeout, 0);
                calaApi_debug("Function called...");
            }, 3000);
        }
    }
    oReq.send(_data);
}

/*********************************************/
/* Function to debug                         */
/* Requieres msg                             */
/*********************************************/
function calaApi_debug(msg){
    if(calaApi_debugMode){
        console.log("[CalApi]->" + msg);
    }
}

/*********************************************/
/* Function to do somethong after some time  */
/* Requieres function, time                  */
/*********************************************/
function calaApi_doSomethingAfter(f, t = 1000){
    var timer = setTimeout(function(){
        f();
        clearTimeout(timer);
    }, t);
}

/*********************************************/
/* Function to do somethong after some time  */
/* Requieres function, time                  */
/*********************************************/
function calaApi_resultToMsg(r){
    if(r == ERROR_NO_VALID_USER){
        return "No valid user, the user may not exist"; 
    }else if(r == ERROR_USER_WRONG_LOGIN_INFO){
        return "Wrong login info";
    }else if(r == ERROR_USER_NO_VALID_SESSION){
        return "No valid session, maybe is too late";
    }else if(r == ERROR_USER_ACCESS_DENIED){
        return "The user is banned 'status' = 0";
    }else if(r == ERROR_USER_EXISTS){
        return "";
    }else if(r == ERROR_USERS_NO_TOKEN){
        return "Error with token";
    }else if(r == ERROR_DB_NO_RESULTS_FOUND){
        return "No results found in db query";
    }else if(r == ERROR_BAD_REQUEST){
        return "Bad request, are all params good?";
    }else if(r == ERROR_ERROR){
        return "Standard error";
    }else if(r == ERROR_MODULE_UNDEFINED){
        return "There is no module to ask or run, 'w' param is not setted";
    }else if(r == ERROR_MODULE_NOT_FOUND){
        return "The module in 'w' does not exist";
    }else if(r == ERROR_FUNCTION_NOT_FOUND){
        return "The function in param 'r' not found";
    }else if(r == SUCCESS_ALL_GOOD){
        return "The request was successful";
    }else{
        return "Is this an error? => " + r;
    }
}