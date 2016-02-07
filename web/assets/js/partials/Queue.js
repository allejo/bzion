/**
 * A Queue for actions that take some time
 */
var Queue = function() {
    var lastPromise = null;
    var count = 0;

    var setCount = function(d) {
        count = count + d;
    };

    var setup = function() {
        var queueDeferred = $.Deferred();

        // when the previous method returns, resolve this one
        $.when(lastPromise).always(function() {
            queueDeferred.resolve();
        });

        return queueDeferred.promise();
    };

    this.add = function(callback) {
        // Increase count when a callback is added to the queue and decrease it
        // when it's over
        setCount(1);

        var methodDeferred = $.Deferred();
        var queueDeferred = setup();

        methodDeferred.always(function() {
            setCount(-1);
        });

        // execute next queue method
        queueDeferred.done(function() {
            if (callback.apply(methodDeferred)) {
                methodDeferred.resolve();
            }
        });

        lastPromise = methodDeferred.promise();
    };

    /**
     * Whether the currently running callback is the last in the queue
     * @return boolean
     */
    this.isLast = function() {
        return count <= 1;
    };
};