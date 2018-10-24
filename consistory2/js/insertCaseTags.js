function setMultiple(el)
{
  var myEl = document.getElementById(el);
  myEl.setAttribute('multiple', 'multiple');
  myEl.setAttribute('size', '15');
}

//Allows insertion of options at cursor position
// Adapted from phpMyAdmin code. Licensed under the GPL

function insertCaseTag() {
    var myForm = document.getElementById('person_form');
    var myNotes = document.getElementById('annotation');
    var scrollPosition = myNotes.scrollTop;
    var myListBox = document.getElementById('caseTags');
    var selectedTags = new String("");
    var myEl = document.getElementById('caseTags');
    myEl.removeAttribute('multiple', '');
    myEl.removeAttribute('size', '');

    if(myListBox.options.length > 0) {
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++) {
            if (myListBox.options[i].selected){
                NbSelect++;
                selectedTags += '~~~' + myListBox.options[i].text + "\n";
            }
        }
        selectedTags = selectedTags.toUpperCase(selectedTags);

       //IE support
       if (document.selection) {
            myNotes.focus();
            sel = document.selection.createRange();
            sel.text = selectedTags;
            myForm.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (myNotes.selectionStart || myNotes.selectionStart == "0") {
            var startPos = myNotes.selectionStart;
            var endPos = myNotes.selectionEnd;
            var strTags = myNotes.value;

            myNotes.value = strTags.substring(0, startPos)
            + selectedTags
            + strTags.substring(endPos, strTags.length);
        } else {
            myNotes.value += selectedTags;
        }
        myNotes.focus();
        myNotes.scrollTop = scrollPosition;
   }
}


function insertCaseTagFromChild() {
    var myForm = opener.document.getElementById('person_form');
    var myNotes = opener.document.getElementById('annotation');
    var scrollPosition = myNotes.scrollTop;
    var myCheckBoxes = document.getElementById('tagCheckboxes');
    var selectedTags = new String("");
    var myEl = document.getElementById('caseTags');

    if(myCheckBoxes.length > 0) {
        var NbSelect = 0;
        for(var i=0; i < myCheckBoxes.length; i++) {
            if (myCheckBoxes[i].checked){
                NbSelect++;
                selectedTags += '~~~' + myCheckBoxes[i].value + "\n";
            }
        }
        selectedTags = selectedTags.toUpperCase(selectedTags);
       //IE support
       if (document.selection) {
            myNotes.focus();
            sel = document.selection.createRange();
            sel.text = selectedTags;
            myForm.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (myNotes.selectionStart || myNotes.selectionStart == "0") {
            var startPos = myNotes.selectionStart;
            var endPos = myNotes.selectionEnd;
            var strTags = myNotes.value;

            myNotes.value = strTags.substring(0, startPos)
            + selectedTags
            + strTags.substring(endPos, strTags.length);
        } else {
            myNotes.value += selectedTags;
        }
        myNotes.focus();
        myNotes.scrollTop = scrollPosition;
   }
   window.close();
}