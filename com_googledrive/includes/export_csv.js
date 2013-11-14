/*!
 * Pines Framework Google Drive JavaScript
 * Component/googledrive
 * http://pinesframework.org
 *
 * Copyright 2013, SciActive
 * http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

// Need this so we can set the rows which will be uploaded to Google Drive
var ROWS;
// Need to set this to globalize the loader
var loader;
      
function setRows(rows) {
    ROWS = unescape(encodeURIComponent(rows));
}
/**
       * Check if the current user has authorized the application.
       * Need to call this when user clicks on Export to Google Drive
       */
      
function checkAuth() {
    gapi.auth.authorize(
    {
        'client_id': CLIENT_ID, 
        'scope': SCOPES, 
        'immediate': true
    },
    handleAuthResult);
}
      
/**
       * Called when authorization server replies.
       *
       * @param {Object} authResult Authorization result.
       */
function handleAuthResult(authResult) {
    if (authResult && !authResult.error) {
        uploadFile(ROWS);
    } else {
        // No access token could be retrieved, show the button to start the authorization flow.
        gapi.auth.authorize(
        {
            'client_id': CLIENT_ID, 
            'scope': SCOPES, 
            'immediate': false
        },
        handleAuthResult);
    }
}

/**
       * Start the file upload.
       *
       * @param {Object} evt Arguments from the file selector.
       */
function uploadFile(csv) {
    loader = $.pnotify({
        text: 'Uploading File...',
        icon: 'picon picon-throbber'
    });
    gapi.client.load('drive', 'v2', function() {
        insertFile(csv);
    });
}

/**
       * Insert new file.
       *
       * @param {File} fileData File object to read data from.
       * @param {Function} callback Function to call when the request is complete.
       */
function insertFile(csv, callback) {
    const boundary = '-------314159265358979323846';
    const delimiter = "\r\n--" + boundary + "\r\n";
    const close_delim = "\r\n--" + boundary + "--";

    
    
    var contentType = 'text/csv';
    var date = new Date();
    var metadata = {
        'title': 'test_csv' + date.getTime(),
        'mimeType': contentType
    };

    var base64Data = btoa(csv);
    var multipartRequestBody =
    delimiter +
    'Content-Type: application/json\r\n\r\n' +
    JSON.stringify(metadata) +
    delimiter +
    'Content-Type: ' + contentType + '\r\n' +
    'Content-Transfer-Encoding: base64\r\n' +
    '\r\n' +
    base64Data +
    close_delim;

    var request = gapi.client.request({
        'path': '/upload/drive/v2/files?convert=true',
        'method': 'POST',
        'params': {
            'uploadType': 'multipart'
        },
        'headers': {
            'Content-Type': 'multipart/mixed; boundary="' + boundary + '"'
        },
        'body': multipartRequestBody
    });
    if (!callback) {
        callback = function(file) {
            loader.pnotify_remove();
            alert("File uploaded");
            // To open the newly created Google Doc in a new tab/window
            window.open(file.alternateLink, '_blank');
        };
    }
    request.execute(callback);
}