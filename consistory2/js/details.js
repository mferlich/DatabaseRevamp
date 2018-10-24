/*
Show and hide navigation items depending on whether or not form data has changed
*/
window.onfocus = init;
window.onload = init;

function init() {
  var x = document.getElementById('person_form').elements;
  for (var i=0;i<x.length;i++)
  {
    if (x[i].id != 'caseTags')
    {
      x[i].onchange = warnChanges;
    }
  }
  document.getElementById('person_form').onreset = cancelChanges;
  document.getElementById('person_form').onsubmit = okToLeave;
}

function okToLeave()
{
  window.onbeforeunload = null;
  window.onunload = null;
}

/* submit a form from a navigation link outside the form */
function submitForm(FormID, id, action) {

  window.onbeforeunload = null;
  window.onunload = null;

  var form = document.getElementById(FormID);

  var navId = document.createElement('input');
  navId.setAttribute('type', 'hidden');
  navId.setAttribute('name', 'nav_id');
  navId.setAttribute('value', id);
  form.appendChild(navId);

  var submitAction = document.createElement('input');
  submitAction.setAttribute('type', 'hidden');
  submitAction.setAttribute('name', action);
  submitAction.setAttribute('value', action);
  form.appendChild(submitAction);

  form.submit();
  return;
}

function checkBeforeUnload(e)
{
  window.onunload = null;
  return "          !! YOU HAVE UNSAVED CHANGES !!          \n"
       + " ** IF YOU CLICK 'OK' YOU WILL LOSE YOUR EDITS **";
}

function checkOnUnload(e)
{
  var submit_form = 0;
  submit_form = confirm('    !! YOU HAVE UNSAVED CHANGES !!\n'
                  + '  * Click OK/Yes to save your work to the database\n'
                  + '  * Click Cancel/No to quit without saving');
  if (submit_form) {
      submitForm('person_form', 0, 'save_on_exit');
      alert('Changes Saved');
  } else {
    alert('Changes NOT Saved');
  }
  return;
}



function warnChanges()
{
/*  window.onbeforeunload = checkBeforeUnload;*/
  window.onunload = checkOnUnload;

  var x = document.getElementById('nav');
      x.style.display = "none";
  var y = document.getElementById('searchBox1');
      y.style.display = "none";
  var z = document.getElementById('warn_changes');
      z.style.display = "block";
}

function cancelChanges()
{
  window.onbeforeunload = null;
  window.onunload = null;

  var x = document.getElementById('nav');
      x.style.display = "block";
  var y = document.getElementById('searchBox1');
      y.style.display = "block";
  var z = document.getElementById('warn_changes');
      z.style.display = "none";
}


function openNotesPopup(url, id)
{
    confirmSubmitOnChanges = false;
    confirmPopup = true;
    change_flag_el = document.getElementById('warn_changes');
    if (change_flag_el.style.display == 'block')
    {
        confirmSubmitOnChanges = confirm('Do you want to SAVE UNSAVED CHANGES in the main window?\n'
                         + '*Click OK/Yes to save before opening the popup.\n'
                         + '*Click Cancel/No to discard those changes.\n');
        confirmPopup = confirm('Do you still want to open the popup window?');
    }

    if (confirmSubmitOnChanges)
    {
            submitForm('person_form', id, 'update_review');
    }

    if (confirmPopup)
    {
        newWin = window.open(url,
                        'notesWin' + id,
                        'directories=no,location=no,menubar=no,resizable=yes,'
                        + 'scrollbars=yes,status=no,toolbar=no,screenX=0,'
                        + 'screenY=0,top=0,left=0');
        newWin.resizeTo(screen.availWidth,screen.availHeight);
    }
    return;
}

function openTagsPopup(url, height, width)
{
    tagsWin = window.open(url, 'insertTagsWin',
                    'directories=no,location=no,menubar=no,resizable=yes,'
                    + 'scrollbars=yes,status=no,toolbar=no,screenX=100,'
                    + 'screenY=100,top=100,left=100');
    tagsWinWidth = width;
    tagsWinHeight = height;
    tagsWin.resizeTo(tagsWinWidth,tagsWinHeight);
    return
}