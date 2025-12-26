dragActive = false; // wird momentan eine Figur verschoben
dragObj = {}; // das Array mit allen Figuren (wird im init() gemacht)
ie = (document.all) ? 1 : 0; // ie oder mozi?

document.onmousedown = mouseDown;
document.onmousemove = mouseMove;
document.onmouseup   = mouseUp;

function mouseDown(e) {
	if(ie) {
		xFrom = event.x;
		yFrom = event.y;
	} else {
		xFrom = e.pageX;
		yFrom = e.pageY;
	}

	for (i=0; i<dragObj.length; i++) {
		if(
			xFrom >= dragObj[i].offsetLeft && xFrom <= dragObj[i].offsetLeft+60 &&
			yFrom >= dragObj[i].offsetTop  && yFrom <= dragObj[i].offsetTop+60
		) {
			dragActive = true;
			dragClickX = xFrom - dragObj[i].offsetLeft;
			dragClickY = yFrom - dragObj[i].offsetTop;
			break;
		}
	}
}

function mouseMove(e) {
	if(dragActive) {
		if(ie) {
			dragObj[i].style.left = event.x - dragClickX;
			dragObj[i].style.top  = event.y - dragClickY;
		} else {
			dragObj[i].style.left = e.pageX - dragClickX;
			dragObj[i].style.top  = e.pageY - dragClickY;
		}

		return false;
	}
}

function mouseUp(e) {
	if(dragActive) {
		dragActive = false;

		if(ie) {
			xTo = event.x; yTo = event.y;
		} else {
			xTo = e.pageX; yTo = e.pageY;
		}

		xFrom = Math.ceil(xFrom / 64);
		yFrom = 9 - Math.ceil((yFrom-45) / 64);
		xTo = Math.ceil(xTo / 64);
		yTo = 9 - Math.ceil((yTo-45) / 64);

		if(turnBoard) {
			xFrom = 9 - xFrom;
			yFrom = 9 - yFrom
			xTo = 9 - xTo;
			yTo = 9 - yTo;
		}

		document.board.xfrom.value = xFrom;
		document.board.yfrom.value = yFrom;
		document.board.xto.value = xTo;
		document.board.yto.value = yTo;
		document.board.submit();
	}
}
