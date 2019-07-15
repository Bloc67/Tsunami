// *** smc_Toggle class.
function tsu_Toggle(oOptions)
{
	this.opt = oOptions;
	this.bCollapsed = false;
	this.oCookie = null;
	this.init();
}

tsu_Toggle.prototype.init = function ()
{
	// The master switch can disable this toggle fully.
	if ('bToggleEnabled' in this.opt && !this.opt.bToggleEnabled)
		return;

	// If cookies are enabled and they were set, override the initial state.
	if ('oCookieOptions' in this.opt && this.opt.oCookieOptions.bUseCookie)
	{
		// Initialize the cookie handler.
		this.oCookie = new smc_Cookie({});

		// Check if the cookie is set.
		var cookieValue = this.oCookie.get(this.opt.oCookieOptions.sCookieName)
		if (cookieValue != null)
			this.opt.bCurrentlyCollapsed = cookieValue == '1';
	}

	// If the init state is set to be collapsed, collapse it.
	if (this.opt.bCurrentlyCollapsed)
		this.changeState(true, true);

	// Initialize the images to be clickable.
	if ('aSwapIconClass' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapIconClass.length; i < n; i++)
		{
			var oImage = document.getElementById(this.opt.aSwapIconClass[i].sId);
			if (typeof(oImage) == 'object' && oImage != null)
			{
				// Display the image in case it was hidden.
				if (oImage.style.display == 'none')
					oImage.style.display = '';

				oImage.instanceRef = this;
				oImage.onclick = function () {
					this.instanceRef.toggle();
					this.blur();
				}
				oImage.style.cursor = 'pointer';
			}
		}
	}
}

// Collapse or expand the section.
tsu_Toggle.prototype.changeState = function(bCollapse, bInit)
{
	// Default bInit to false.
	bInit = typeof(bInit) == 'undefined' ? false : true;

	// Handle custom function hook before collapse.
	if (!bInit && bCollapse && 'funcOnBeforeCollapse' in this.opt)
	{
		this.tmpMethod = this.opt.funcOnBeforeCollapse;
		this.tmpMethod();
		delete this.tmpMethod;
	}

	// Handle custom function hook before expand.
	else if (!bInit && !bCollapse && 'funcOnBeforeExpand' in this.opt)
	{
		this.tmpMethod = this.opt.funcOnBeforeExpand;
		this.tmpMethod();
		delete this.tmpMethod;
	}

	// Loop through all the images that need to be toggled.
	if ('aSwapIconClass' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapIconClass.length; i < n; i++)
		{
			var oImage = document.getElementById(this.opt.aSwapIconClass[i].sId);
			if (typeof(oImage) == 'object' && oImage != null)
			{
				// Only (re)load the image if it's changed.
				var sTargetSource = bCollapse ? this.opt.aSwapIconClass[i].classCollapsed : this.opt.aSwapIconClass[i].classExpanded;
				if (oImage.className != sTargetSource)
					oImage.className = sTargetSource;
			}
		}
	}

	// swap styles
	if ('aSwapStyles' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapStyles.length; i < n; i++)
		{
			var x = document.getElementsByClassName(this.opt.aSwapStyles[i].sId);
			var y;
			for (y = 0; y < x.length; y++) {
				if(bCollapse) {
					x[y].classList.add(this.opt.aSwapStyles[i].classSwap);
				}
				else {
					x[y].classList.remove(this.opt.aSwapStyles[i].classSwap);
				}
			}
		}
	}

	// swap containers 
	for (var i = 0, n = this.opt.aSwappableContainers.length; i < n; i++)
	{
		if (this.opt.aSwappableContainers[i] == null)
			continue;

		var x = document.getElementsByClassName(this.opt.aSwappableContainers[i]);
		var y;
		for (y = 0; y < x.length; y++) {
			x[y].style.display = bCollapse ? 'none' : '';
		}
	}

	// Update the new state.
	this.bCollapsed = bCollapse;

	// Update the cookie, if desired.
	if ('oCookieOptions' in this.opt && this.opt.oCookieOptions.bUseCookie)
		this.oCookie.set(this.opt.oCookieOptions.sCookieName, this.bCollapsed ? '1' : '0');

	if (!bInit && 'oThemeOptions' in this.opt && this.opt.oThemeOptions.bUseThemeSettings)
		smf_setThemeOption(this.opt.oThemeOptions.sOptionName, this.bCollapsed ? '1' : '0', 'sThemeId' in this.opt.oThemeOptions ? this.opt.oThemeOptions.sThemeId : null, this.opt.oThemeOptions.sSessionId, this.opt.oThemeOptions.sSessionVar, 'sAdditionalVars' in this.opt.oThemeOptions ? this.opt.oThemeOptions.sAdditionalVars : null);
}

tsu_Toggle.prototype.toggle = function()
{
	// Change the state by reversing the current state.
	this.changeState(!this.bCollapsed);
}



// The purpose of this code is to fix the height of overflow: auto blocks, because some browsers can't figure it out for themselves.
function smf_codeBoxFix()
{
	var codeFix = document.getElementsByTagName('code');
	for (var i = codeFix.length - 1; i >= 0; i--)
	{
		if (is_webkit && codeFix[i].offsetHeight < 20)
			codeFix[i].style.height = (codeFix[i].offsetHeight + 20) + 'px';

		else if (is_ff && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0))
			codeFix[i].style.overflow = 'scroll';

		else if ('currentStyle' in codeFix[i] && codeFix[i].currentStyle.overflow == 'auto' && (codeFix[i].currentStyle.height == '' || codeFix[i].currentStyle.height == 'auto') && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0) && (codeFix[i].offsetHeight != 0))
			codeFix[i].style.height = (codeFix[i].offsetHeight + 24) + 'px';
	}
}

// Add a fix for code stuff?
if ((is_ie && !is_ie4) || is_webkit || is_ff)
	addLoadEvent(smf_codeBoxFix);

// Toggles the element height and width styles of an image.
function smc_toggleImageDimensions()
{
	var oImages = document.getElementsByTagName('IMG');
	for (oImage in oImages)
	{
		// Not a resized image? Skip it.
		if (oImages[oImage].className == undefined || oImages[oImage].className.indexOf('bbc_img resized') == -1)
			continue;

		oImages[oImage].style.cursor = 'pointer';
		oImages[oImage].onclick = function() {
			this.style.width = this.style.height = this.style.width == 'auto' ? null : 'auto';
		};
	}
}

// Add a load event for the function above.
addLoadEvent(smc_toggleImageDimensions);

// Adds a button to a certain button strip.
function smf_addButton(sButtonStripId, bUseImage, oOptions)
{
	var oButtonStrip = document.getElementById(sButtonStripId);
	var aItems = oButtonStrip.getElementsByTagName('span');

	// Remove the 'last' class from the last item.
	if (aItems.length > 0)
	{
		var oLastSpan = aItems[aItems.length - 1];
		oLastSpan.className = oLastSpan.className.replace(/\s*last/, 'position_holder');
	}

	// Add the button.
	var oButtonStripList = oButtonStrip.getElementsByTagName('ul')[0];
	var oNewButton = document.createElement('li');
	setInnerHTML(oNewButton, '<a href="' + oOptions.sUrl + '" ' + ('sCustom' in oOptions ? oOptions.sCustom : '') + '><span class="last"' + ('sId' in oOptions ? ' id="' + oOptions.sId + '"': '') + '>' + oOptions.sText + '</span></a>');

	oButtonStripList.appendChild(oNewButton);
}

// Adds hover events to list items. Used for a versions of IE that don't support this by default.
var smf_addListItemHoverEvents = function()
{
	var cssRule, newSelector;

	// Add a rule for the list item hover event to every stylesheet.
	for (var iStyleSheet = 0; iStyleSheet < document.styleSheets.length; iStyleSheet ++)
		for (var iRule = 0; iRule < document.styleSheets[iStyleSheet].rules.length; iRule ++)
		{
			oCssRule = document.styleSheets[iStyleSheet].rules[iRule];
			if (oCssRule.selectorText.indexOf('LI:hover') != -1)
			{
				sNewSelector = oCssRule.selectorText.replace(/LI:hover/gi, 'LI.iehover');
				document.styleSheets[iStyleSheet].addRule(sNewSelector, oCssRule.style.cssText);
			}
		}

	// Now add handling for these hover events.
	var oListItems = document.getElementsByTagName('LI');
	for (oListItem in oListItems)
	{
		oListItems[oListItem].onmouseover = function() {
			this.className += ' iehover';
		};

		oListItems[oListItem].onmouseout = function() {
			this.className = this.className.replace(new RegExp(' iehover\\b'), '');
		};
	}
}

// Add hover events to list items if the browser requires it.
if (is_ie7down && 'attachEvent' in window)
	window.attachEvent('onload', smf_addListItemHoverEvents);


// Changed Iconlist 
var bIconLists = new Array();

// *** IconList object.
function bIconList(oOptions)
{
	if (!window.XMLHttpRequest)
		return;

	this.opt = oOptions;
	this.bListLoaded = false;
	this.oContainerDiv = null;
	this.funcMousedownHandler = null;
	this.funcParent = this;
	this.iCurMessageId = 0;
	this.iCurTimeout = 0;

	// Add backwards compatibility with old themes.
	if (!('sSessionVar' in this.opt))
		this.opt.sSessionVar = 'sesc';

	this.initIcons();
}

// Replace all message icons by icons with hoverable and clickable div's.
bIconList.prototype.initIcons = function ()
{
	for (var i = document.images.length - 1, iPrefixLength = this.opt.sIconIdPrefix.length; i >= 0; i--)
		if (document.images[i].id.substr(0, iPrefixLength) == this.opt.sIconIdPrefix)
			setOuterHTML(document.images[i], '<div title="' + this.opt.sLabelIconList + '" onclick="' + this.opt.sBackReference + '.openPopup(this, ' + document.images[i].id.substr(iPrefixLength) + ')" onmouseover="' + this.opt.sBackReference + '.onBoxHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onBoxHover(this, false)" style="background: ' + this.opt.sBoxBackground + '; cursor: ' + (is_ie && !is_ie6up ? 'hand' : 'pointer') + '; text-align: center;"><img src="' + document.images[i].src + '" alt="' + document.images[i].alt + '" id="' + document.images[i].id + '" style="margin: 0px;" /></div>');
}

// Event for the mouse hovering over the original icon.
bIconList.prototype.onBoxHover = function (oDiv, bMouseOver)
{
	oDiv.style.background = bMouseOver ? this.opt.sBoxBackgroundHover : this.opt.sBoxBackground;
}

// Show the list of icons after the user clicked the original icon.
bIconList.prototype.openPopup = function (oDiv, iMessageId)
{
	this.iCurMessageId = iMessageId;

	if (!this.bListLoaded && this.oContainerDiv == null)
	{
		// Create a container div.
		this.oContainerDiv = document.createElement('div');
		this.oContainerDiv.id = 'iconList';
		this.oContainerDiv.style.display = 'none';
		this.oContainerDiv.style.cursor = is_ie && !is_ie6up ? 'hand' : 'pointer';
		this.oContainerDiv.style.position = 'absolute';
		this.oContainerDiv.style.width = oDiv.offsetWidth + 'px';
		this.oContainerDiv.style.background = this.opt.sContainerBackground;
		this.oContainerDiv.style.border = this.opt.sContainerBorder;
		this.oContainerDiv.style.padding = '1px';
		this.oContainerDiv.style.textAlign = 'center';
		document.body.appendChild(this.oContainerDiv);

		// Start to fetch its contents.
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=xmlhttp;sa=messageicons;board=' + this.opt.iBoardId + ';xml', this.onIconsReceived);
		delete this.tmpMethod;

		createEventListener(document.body);
	}

	// Set the position of the container.
	var aPos = smf_itemPos(oDiv);
	if (is_ie50)
		aPos[1] += 4;

	this.oContainerDiv.style.top = (aPos[1] + oDiv.offsetHeight) + 'px';
	this.oContainerDiv.style.left = (aPos[0] - 1) + 'px';
	this.oClickedIcon = oDiv;

	if (this.bListLoaded)
		this.oContainerDiv.style.display = 'block';

	document.body.addEventListener('mousedown', this.onWindowMouseDown, false);
}

// Setup the list of icons once it is received through xmlHTTP.
bIconList.prototype.onIconsReceived = function (oXMLDoc)
{
	var icons = oXMLDoc.getElementsByTagName('smf')[0].getElementsByTagName('icon');
	var sItems = '';

	for (var i = 0, n = icons.length; i < n; i++)
		sItems += '<div onmouseover="' + this.opt.sBackReference + '.onItemHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onItemHover(this, false);" onmousedown="' + this.opt.sBackReference + '.onItemMouseDown(this, \'' + icons[i].getAttribute('value') + '\');" style="margin-left: auto; margin-right: auto; border: ' + this.opt.sItemBorder + '; background: ' + this.opt.sItemBackground + '"><img src="' + icons[i].getAttribute('url') + '" alt="' + icons[i].getAttribute('name') + '" title="' + icons[i].firstChild.nodeValue + '" /></div>';

	setInnerHTML(this.oContainerDiv, sItems);
	this.oContainerDiv.style.display = 'block';
	this.bListLoaded = true;

	if (is_ie)
		this.oContainerDiv.style.width = this.oContainerDiv.clientWidth + 'px';

	ajax_indicator(false);
}

// Event handler for hovering over the icons.
bIconList.prototype.onItemHover = function (oDiv, bMouseOver)
{
	oDiv.style.background = bMouseOver ? this.opt.sItemBackgroundHover : this.opt.sItemBackground;
	oDiv.style.border = bMouseOver ? this.opt.sItemBorderHover : this.opt.sItemBorder;
	if (this.iCurTimeout != 0)
		window.clearTimeout(this.iCurTimeout);
	if (bMouseOver)
		this.onBoxHover(this.oClickedIcon, true);
	else
		this.iCurTimeout = window.setTimeout(this.opt.sBackReference + '.collapseList();', 500);
}

// Event handler for clicking on one of the icons.
bIconList.prototype.onItemMouseDown = function (oDiv, sNewIcon)
{
	if (this.iCurMessageId != 0)
	{
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		var oXMLDoc = this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=jsmodify;topic=' + this.opt.iTopicId + ';msg=' + this.iCurMessageId + ';' + this.opt.sSessionVar + '=' + this.opt.sSessionId + ';icon=' + sNewIcon + ';xml');
		delete this.tmpMethod;
		ajax_indicator(false);

		var oMessage = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('message')[0];
		if (oMessage.getElementsByTagName('error').length == 0)
		{
			if (this.opt.bShowModify && oMessage.getElementsByTagName('modified').length != 0)
				setInnerHTML(document.getElementById('modified_' + this.iCurMessageId), oMessage.getElementsByTagName('modified')[0].childNodes[0].nodeValue);
			this.oClickedIcon.getElementsByTagName('img')[0].src = oDiv.getElementsByTagName('img')[0].src;
		}
	}
}

// Event handler for clicking outside the list (will make the list disappear).
bIconList.prototype.onWindowMouseDown = function ()
{
	for (var i = aIconLists.length - 1; i >= 0; i--)
	{
		aIconLists[i].funcParent.tmpMethod = aIconLists[i].collapseList;
		aIconLists[i].funcParent.tmpMethod();
		delete bIconLists[i].funcParent.tmpMethod;
	}
}

// Collapse the list of icons.
bIconList.prototype.collapseList = function()
{
	this.onBoxHover(this.oClickedIcon, false);
	this.oContainerDiv.style.display = 'none';
	this.iCurMessageId = 0;
	document.body.removeEventListener('mousedown', this.onWindowMouseDown, false);
}
