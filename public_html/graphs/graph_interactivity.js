function vertical_move(classIDList,distance) {
    var classIDs = classIDList.split(" ");
    for (var k = 0; k < classIDs.length; k++) {
        var elementList = document.getElementsByClassName(classIDs[k]);
        for (var i = 0; i < elementList.length; i++) {
            var attrValue = "translate(" + distance + ", 0)";
            elementList[i].style.display = elementList[i].setAttribute('transform', attrValue);
        }
    }
}

function toggledisplay_by_class(classIDList, override) {
    var classIDs = classIDList.split(" ");
    for (var k = 0; k < classIDs.length; k++) {
        var elementList = document.getElementsByClassName(classIDs[k]);
        for (var i = 0; i < elementList.length; i++) {
            if (override) {
                elementList[i].style.display = override === 'hide' ? 'none' : '';
            } else {
                elementList[i].style.display = elementList[i].style.display === 'none' ? '' : 'none';
            }
        }
    }
    additional_hack1();
}

function e_colourchange() {
    var classIDs = document.getElementById('e_target').value.split(" ");
    for (var k = 0; k < classIDs.length; k++) {
        var elementList = document.getElementsByClassName(classIDs[k]);
        for (var i = 0; i < elementList.length; i++) {
            var parameters = document.getElementById('e_colour').value.split(" ");
            elementList[i].style.fill = parameters[0];
        }
    }
}

function colorchange(elementID, leave_white) {
    if (leave_white==="yes") { return; }
    var togglecolour = "rgb(200, 200, 200)";
    if (document.getElementById(elementID).style.backgroundColor === togglecolour) {
        document.getElementById(elementID).style.backgroundColor = "";
    } else {
        document.getElementById(elementID).style.backgroundColor = togglecolour;
    }
}

function make_buttons(id, target, label, cssclass, space_after, div_id, space_before) {
    var newBtn = document.createElement("BUTTON");
    var text = document.createTextNode(label);
    newBtn.appendChild(text);
    newBtn.id = id;
    newBtn.onclick = function() {
        toggledisplay_by_class(target);
        colorchange(id);
    };
    newBtn.className = cssclass;
    var text = document.createTextNode(space_before);
    document.getElementById(div_id).appendChild(text);
    document.getElementById(div_id).appendChild(newBtn);
    var text = document.createTextNode(space_after);
    document.getElementById(div_id).appendChild(text);
}

function add_content(content, div_id) {
    var text = document.createTextNode(content);
    document.getElementById(div_id).appendChild(text);
}

// resize_svg isn't actually used in the wordpress version of the stuff because I altered the svg replacement plugin to do that.
function resize_svg(size, number) {
    var shape = document.getElementsByTagName("svg")[number];
    var rect = shape.getBoundingClientRect();
    shape.setAttribute("viewBox", "0 0 " + rect.width + " " + rect.height); 
    shape.style.width=size;
}
