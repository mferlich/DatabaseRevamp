window.onload = window.opener.location.reload(true);

function warnChangesMain()
{
    var change_flag_el = window.opener.document.getElementById('warn_changes');
    var form = document.getElementById('annotation_form');
    var opener = window.opener.location

    if (change_flag_el.style.display == 'block')
    {
        overwrite = confirm("You have unsaved changes in the main window. "
                    + "They will be lost if you submit these changes. \n"
                    + "*Click Yes/OK to overwrite with information from this window.\n"
                    + "*Click No/Cancel to cancel this request.");
        if (!overwrite)
        {
           return false;
        }
    }
    cancelOpenerChanges();
    form.submit();
    window.opener.location.reload(true);

/* if we've gotten this far, we return false to cancel the
click and stop the regular submit */
    return true;
}

function cancelOpenerChanges()
{
  window.opener.onbeforeunload = null;
  window.opener.onunload = null;

  var x = window.opener.document.getElementById('nav');
      x.style.display = "block";
  var y = window.opener.document.getElementById('searchBox1');
      y.style.display = "block";
  var z = window.opener.document.getElementById('warn_changes');
      z.style.display = "none";
}