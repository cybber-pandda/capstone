// public/assets/dashboard/js/easymde.js

"use strict";

let easyMDE = null;

window.initializeEasyMDE = function () {
    const textarea = document.querySelector("#content");

    if (textarea) {
        if (easyMDE) {
            easyMDE.toTextArea(); // Destroy old instance
        }

        easyMDE = new EasyMDE({
            element: textarea,
            spellChecker: false,
            toolbar: [
                "bold",
                "italic",
                "heading",
                "|",
                "quote",
                "unordered-list",
                "ordered-list",
                "|",
                // "link",
                // "image",
                // "|",
                // "preview",
                // "side-by-side",
                "fullscreen",
                //"|",
               // "guide",
            ],
        });
    }
}
