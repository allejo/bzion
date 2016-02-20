// Courtesy of: http://graphicdesign.stackexchange.com/a/57280/20292

var doc = app.activeDocument;
var symbolCount = doc.symbols.length;

if (symbolCount >= 1) {

    if (confirm("Are all your layers hidden?")) {

        // create temp layer
        doc.layers.add();

        // create directory
        var dest = Folder.selectDialog();
        dest.create();

        // loop through symbols
        for (var i = 0; i < doc.symbols.length; i++) {

            // place a symbol instance - temp
            var s = doc.symbolItems.add(doc.symbols[i]);

            // assign name
            var filename = (doc.symbols[i].name)

            // export symbols
            savePNG(dest, filename);

            // delete temp symbol instance
            s.remove();
        }
        // remove temp layer
        doc.layers[0].remove();
    }

    function savePNG(dest, filename) {
        // save options
        var type = ExportType.PNG24;
        var options = new ExportOptionsPNG24();
        options.transparency = true;

        // file
        var file = new File(dest + "/" + filename);

        // export
        doc.exportFile(file, type, options);
    }

} else {
    alert("You don't have any symbols in this document");
}
