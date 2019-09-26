/* toggle CSS classes */
function addclass(id, toggleclass, ids)
{
	var element = document.getElementById(id);
	var x = document.getElementsByClassName(ids);
	var i;
	for (i = 0; i < x.length; i++) {
		if(x[i] != element) {
			x[i].classList.remove(toggleclass);
		}
	}
	element.classList.toggle(toggleclass);	
}

function addclass2(id, toggleclass, id2, toggleclass2)
{
	var element = document.getElementById(id);
	var element2 = document.getElementById(id2);
	element.classList.toggle(toggleclass);	
	element2.classList.toggle(toggleclass2);	
}
