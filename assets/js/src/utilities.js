module.exports = {
    //
    // courtesy of: https://stackoverflow.com/a/21903119/1239484
    //
    getURLParameter: function (sParam) {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');

        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam)
            {
                return sParameterName[1];
            }
        }

        return false;
    },
    //
    // Courtesy of: https://stackoverflow.com/a/10997390/1239484
    //
    setURLParameter: function (param, paramVal, url) {
        if (typeof url === 'undefined') {
            url = window.location.href;
        }

        var tempArray = url.split("?");
        var temp = "";

        var anchor = null;
        var newAdditionalURL = "";
        var baseURL = tempArray[0];
        var additionalURL = tempArray[1];

        var tmpAnchor = "";
        var queryParams = null;

        if (additionalURL)
        {
            tmpAnchor = additionalURL.split("#");
            queryParams = tmpAnchor[0];
            anchor = tmpAnchor[1];

            if (anchor) {
                additionalURL = queryParams;
            }

            tempArray = additionalURL.split("&");

            for (var i = 0; i < tempArray.length; i++) {
                if (tempArray[i].split('=')[0] !== param) {
                    newAdditionalURL += temp + tempArray[i];
                    temp = "&";
                }
            }
        }
        else
        {
            tmpAnchor = baseURL.split("#");
            queryParams = tmpAnchor[0];
            anchor  = tmpAnchor[1];

            if (queryParams) {
                baseURL = queryParams;
            }
        }

        if (anchor) {
            paramVal += "#" + anchor;
        }

        var rows_txt = temp + "" + param + "=" + paramVal;
        return baseURL + "?" + newAdditionalURL + rows_txt;
    },
    //
    // Courtesy of: https://stackoverflow.com/a/4893927/1239484
    //
    removeURLParameter: function (parameter, url) {
        if (typeof url === 'undefined') {
            url = window.location.href;
        }

        var urlParts = url.split('?');

        if (urlParts.length >= 2) {
            // Get first part, and remove from array
            var urlBase = urlParts.shift();

            // Join it back up
            var queryString = urlParts.join('?');

            var prefix = encodeURIComponent(parameter) + '=';
            var parts = queryString.split(/[&;]/g);

            // Reverse iteration as may be destructive
            for (var i = parts.length; i-- > 0; ) {
                // Idiom for string.startsWith
                if (parts[i].lastIndexOf(prefix, 0) !== -1) {
                    parts.splice(i, 1);
                }
            }

            url = urlBase + '?' + parts.join('&');
        }

        return url;
    }
};
