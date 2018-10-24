function setDisplay(el, toggle)
{
  var display = 'block';
  if (!toggle)
  {
   display = 'none';
  }
  div = document.getElementById(el);
  div.style.display = display;
  return;
}