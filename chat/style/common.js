function highlight(currentItem)
{
	var listBox = currentItem.parentNode;
	items = listBox.getElementsByTagName('li');
	for (var i=0;i<items.length ; i++)
	{
	    if (items[i] == currentItem)
	    {
	        items[i].className = 'highlight';
	    }
		else
		{
		    items[i].className = '';
		}
	}
	currentItem.blur();
}