//Enter to search table.
function enterForSearch(){
    document.getElementById("refinement").addEventListener("keydown", function(e){
        if(e.keyCode == 13){
            document.getElementById("set").click();
        }    
    });
	document.getElementById("commentrefine").addEventListener("keydown", function(e){
        if(e.keyCode == 13){
            document.getElementById("set").click();
        }    
    });
	    document.getElementById("rulerefine").addEventListener("keydown", function(e){
        if(e.keyCode == 13){
            document.getElementById("set").click();
        }    
    });
}

window.onload = function(){
	loadFunction();
	enterForSearch();
}
window.onhashchange = function() {
	board_changed = true;
	loadFunction();
}

//Construction of tables.
var global_data = Array();
var board_data = Array();
var working_data = Array();
var archive_URL_Text = "[Archive Link]"
var window_width;
const WINDOW_CONSTANT = 1845;

var table = document.getElementById("bansTable");
var count = document.getElementById("count");
var counter = 0;

var max_counter;
var first_page_max;
var minus_counter = 0;
var current_page;
var actual_page;
var max_page;

var board_refine = "";
var comment_refine = "";
var rule_refine = "";

var init = false;    
var table_built = Array();
var refresh_called = false;
var board_changed = false;

var construct_interval;

var page_load_amount = 1000;

//sets global data in table and count if not set already. First to be called.
function loadFunction(){
    //set a refresh message
    if(init)count.innerHTML = "...Refreshing...";
    //else window.location.hash = '#' + 1;
    //load ledger data
    var xhttp_ledger = new XMLHttpRequest();
    xhttp_ledger.onreadystatechange = ajaxTableSetup;
    xhttp_ledger.open("POST", "Scripts/sql-reader.php", true);
	xhttp_ledger.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
	//#page|board|com|reason
	var hash = window.location.hash; 
	if(hash != ""){
		var hash_details = hash.split("|");
		//error check
		if(hash.indexOf("|") == -1){
			hash_details = hash.split("%7C");
		} 		
		if(hash_details[1] != undefined)
			board_refine = hash_details[1];
		if(hash_details[2] != undefined)
			comment_refine = hash_details[2];
		if(hash_details[3] != undefined)
			rule_refine = hash_details[3];
				
		xhttp_ledger.send("Query=4&Board=" + board_refine + "&Comment=" + comment_refine+ "&Rule=" + rule_refine);
	}
	else {
		xhttp_ledger.send("Query=4&Board=&Comment=&Rule=");
	}
}

function ajaxTableSetup(){
    if (this.readyState == 4 && this.status == 200) {	
				//read the total entries in file
		max_counter = this.responseText;
		max_page = Math.floor(this.responseText/1000);
		first_page_max = this.responseText % 1000;
		first_page_max = first_page_max == 0 ? 1000 : first_page_max;	
		current_page = max_page;
		
        var hash = window.location.hash; 
        if(hash != ""){
            var hash_details = hash.split("|");
			//error check
			if(hash.indexOf("|") == -1){
				hash_details = hash.split("%7C");
			} 							
			if(hash_details[0] != undefined)
				current_page = max_page - hash_details[0].substring(1) + 1;
			if(hash_details[1] != undefined)
				board_refine = hash_details[1];
			if(hash_details[2] != undefined)
				comment_refine = hash_details[2];
			if(hash_details[3] != undefined)
				rule_refine = hash_details[3];
			
			document.getElementById("refinement").value = decodeURI(hash_details[1]);
			document.getElementById("commentrefine").value = decodeURI(hash_details[2]);
			document.getElementById("rulerefine").value = decodeURI(hash_details[3]);
        }
		if(current_page < 0){
			current_page=0;
			actual_page = max_page - current_page + 1;
			window.location.hash = '#' + actual_page + "|" + board_refine + "|" + comment_refine + "|" + rule_refine;
		} 
		else  actual_page = max_page - current_page + 1;
		
        window_width = window.innerWidth;
        //check if loaded
        if(global_data[current_page] === undefined || refresh_called || board_changed){
			if(board_changed){
				global_data.forEach(function(page,index){
					global_data[index] = [];
				});
			}
			board_changed = false;
            //load the log data
            var xhttp_log = new XMLHttpRequest();
            xhttp_log.onreadystatechange = function(){        
                table = document.getElementById("bansTable");
                count = document.getElementById("count");
                if (this.readyState == 4 && this.status == 200) {
                    while(table.hasChildNodes()) table.removeChild(table.lastChild);

					global_data[current_page] = [];
                    sql_data = this.responseText;
                    sql_data = sql_data.split("</>");    
					sql_data.forEach(function(data, index){
						if(data=="") return;
						var row  = data.split("<>");
						row[3] = insertWBR(row[3]);
						global_data[current_page][index] = {board:row[0], name:row[1], trip:row[2],com:row[3],action:row[4],length:row[5],reason:row[6],now:row[7]};
						addArchiveToComment(index, row[8]);
					});
                    table_built[current_page] = false;
                    
                    constructTable();
                    buildPages();

                    init = true;
                    refresh_called = false;
                }
                else if(this.readyState == 4 && this.status != 200){                
                    table.innerHTML = "<tr><td>Page Load Error. Try Changing Pages</td></tr>";
                }
            }
            xhttp_log.open("POST", "Scripts/sql-reader.php", true);
			xhttp_log.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp_log.send("Query=5&Page=" + current_page +"&Board="+ board_refine+"&Comment="+ comment_refine +"&Rule="+ rule_refine);		
        }
        else {
            constructTable();
            buildPages();

            init = true;
            refresh_called = false;
        }
    }
    else if(this.readyState == 4 && this.status != 200){
        table.innerHTML = "<tr><td>Ledger Load Error . Try Refreshing</td></tr>";
    }             
}

function refreshFunction(){
    table_built[current_page] = false;
    refresh_called = true;
    loadFunction();
}
    
function refineFunction(){
    //reloads page from index onhashchange
    board_refine = document.getElementById("refinement").value;
	comment_refine = document.getElementById("commentrefine").value;
	rule_refine = document.getElementById("rulerefine").value;
    if(actual_page === undefined) actual_page = "";
	board_changed = true;
	window.location.hash = '#' + actual_page + "|" + board_refine + "|" + comment_refine + "|" + rule_refine;
}

function insertWBR(row){
	
	var wbr_interval = 40;

	row = row.replace(/<wbr>/g, "");
	var dummy_node = document.createElement("P");
	dummy_node.innerHTML = row; //4chan moderator here. I will BAN the next person who honks
	
	var text_nodes =  [];
	var text_walker=document.createTreeWalker(dummy_node, NodeFilter.SHOW_TEXT);
	var node;
	while(node=text_walker.nextNode()) {
		text_nodes.push(node);
	}
	var len = text_nodes.length;
	var total_index_counter = 0;
	for(var text_node = 0 ; text_node < len; text_node++){
		var text = text_nodes[text_node].nodeValue ;
		if(text != null && !isNaN(text.length)){
			total_index_counter += text.length;
	
			if(total_index_counter > wbr_interval){
				if(text.length < wbr_interval){
					text_nodes[text_node].parentNode.appendChild(document.createElement("WBR"));
				}
				else{
					var replacement = text_nodes[text_node].splitText(wbr_interval);
					text_nodes[text_node].parentNode.insertBefore(document.createElement("WBR"), replacement);
					text_nodes.push(replacement);
					len++;
				}
				total_index_counter = 0;
			}
		}
	}
	return dummy_node.innerHTML;
}
    
function addArchiveToComment(index, filename){
    /**
    Thanks to http://archive.nyafuu.org/ : c / e / n / news / out / p / toy / vip/ vp / w / wg / wsr 
    Thanks to http://4plebs.org/ : adv / hr / o / pol / s4s / sp / trv / tv / x 
    Thanks to http://desuarchive.org : a / aco / an / co / d / fit / gif / his / int / k / m / mlp / qa / r9k / tg / trash / vr / wsg 
    Thanks to http://archive.loveisover.me : i / lgbt / t / u 
    Thanks to https://boards.fireden.net : cm / ic / sci / v / vg / y 
    Thanks to http://archiveofsins.com/ : h / hc / hm / r / s / soc 
    Thanks to http://thebarchive.com : b / bant 
    Thanks to https://rbt.asia : cgl / g / mu 
    */
    var A = "";
    switch(global_data[current_page][index]["board"]){
        case "a": case "cm": case "co":case "ic":case "sci":case "tg":case "v":case "vg":case "y": 
            A = "https://boards.fireden.net/" + global_data[current_page][index]["board"] + "/search/";
        break;
        case "adv": case "f": case "hr":case "o":case "pol":case "s4s":case "sp":case "tg":case "trv":case "tv": case "x":
            A = "https://archive.4plebs.org/" + global_data[current_page][index]["board"] + "/search/";
        break;
        case "aco": case "an": case "c":case "d":case "fit":case "gif":case "his":case "int":case "k":case "m": case "mlp": case "qa":case "r9k":case "trash":case "vr":case "wsg":
            A = "https://desuarchive.org/" + global_data[current_page][index]["board"] + "/search/";
        break;
        case "mu": case "cgl": case "g":
            A = "https://rbt.asia/" + global_data[current_page][index]["board"] + "/search/";
        break;
        case "bant": case "vp": case "c":case "con":case "e":case "n":case "news":case "out":case "p":case "toy":case "vip":case "vp":case "w":case "wg":case "wsr":
            A = "https://archive.nyafuu.org/"  + global_data[current_page][index]["board"] + "/search/";
        break;
        /*case "c": case "d": case "e":*/ case "i": case "lgbt": case "t": case "u":
            A = "https://archive.loveisover.me/"  + global_data[current_page][index]["board"] + "/search/";
        break;
        default: 
            A = "https://archived.moe/" + global_data[current_page][index]["board"] + "/search/";        
    }
    var B = "";
    var comment = global_data[current_page][index]["com"] + "";
    if(comment !== ""){
		var Bi = comment.replace(/<br>/g, " ");
		var dummy_node = document.createElement("P");
        dummy_node.innerHTML = Bi;
        var Bii  = dummy_node.textContent  + " ";
		var Biii = Bii.substr(0,Bii.indexOf(" ", Bii.length > 50 ? 50 : Bii.length - 1)).trim();
		Biv = Biii.replace(/\s+/g, " ");
		B = encodeURIComponent(Biv);
        B = "text/" +  B + "/";
    }
    else B = "";
    var C = (function(){
        var S = global_data[current_page][index]["now"].split("/");
        return "end/20" + S[2].substr(0,2) + "-" + S[0] + "-" + (parseInt(S[1])+1) + "/";        
    })();
    var D 
    if(filename !== undefined)
        D  =  "filename/" + filename;
    else D = "";
    var URL  = A + B + C + D;
    if(B !== ""){
        global_data[current_page][index]["com"] =  global_data[current_page][index]["com"] +  "</br></br><details><summary>" + archive_URL_Text + "</summary><a href = \"" + URL  + "\">" + URL + "</a></details>";
}
    else
        global_data[current_page][index]["com"] =  global_data[current_page][index]["com"] +  "<details><summary>" + archive_URL_Text + "</summary><a href = \"" + URL  + "\">" + URL + "</a></details>";
};
    
function stopFunction(){
    clearInterval(construct_interval);
}
    
var interval_counter = 0;
function buildFullTableInterval(){    
	var len = global_data[current_page].length;
	//current_page == max_page ? counter = 0 : counter = 0;    

    //go through all retrived JSON
    
    var fake_continue = false;
    
    if(interval_counter == -1){
        stopFunction();
		buildEntryCounter(board_refine);
        return;
    }
    
    var tr = document.createElement("TR");
    table.appendChild(tr);
    
    if(!fake_continue){
        counter++;
        //build table with data
        var line = global_data[current_page][interval_counter];
        buildRow(tr, line);   
    }
    interval_counter--;
    if(interval_counter == -1){
        table_built[current_page] = true;
    }
    //change the counter
    buildEntryCounter(board_refine);
}

function buildRow(tr, line){
	
        var width_ratio = window_width / WINDOW_CONSTANT;
        width_ratio = (width_ratio < 0.0 ? 0.0:width_ratio) ;
        
		var vw_size = '1.025vw';
		var px_size = '18.0px'
		
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","boardItem");
        var tdBoard;
        if(line["board"] == "s4s")  tdBoard = document.createTextNode("[" + line["board"] + "]");
        else tdBoard = document.createTextNode("/" + line["board"] + "/");
        td.setAttribute("style", fontSize);
        td.appendChild(tdBoard);
        tr.appendChild(td);
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","nameItem");
        //if(line["name"].length > 10){}
        var tdName = document.createTextNode(line["name"]);
        td.setAttribute("style", fontSize);
        td.appendChild(tdName);
        tr.appendChild(td);
        
        var fontSize = "font-size:" + vw_size + ";"
        if(line["trip"] === undefined || line["trip"] === null) line["trip"] = "";
        var td =  document.createElement("TD");
        td.setAttribute("class","tripItem");
        var tdTrip = document.createTextNode(line["trip"]);
        td.setAttribute("style", fontSize);
        td.appendChild(tdTrip);
        tr.appendChild(td);
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","comItem");
        td.setAttribute("style", fontSize);
        td.innerHTML = (line["com"]);
        tr.appendChild(td);
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","actionItem");
        td.setAttribute("style", fontSize);
        td.innerHTML = (line["action"]);
        tr.appendChild(td);    
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","lengthItem");
        td.setAttribute("style", fontSize);
        td.innerHTML = (line["length"]);
        tr.appendChild(td);    
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","reasonItem");
        td.setAttribute("style", fontSize);
        td.innerHTML = (line["reason"]);
        tr.appendChild(td);    
        
        var fontSize = "font-size:" + vw_size + ";"
        var td =  document.createElement("TD");
        td.setAttribute("class","nowItem");
        td.setAttribute("style", fontSize);
        td.innerHTML = (line["now"]);
        tr.appendChild(td);                
}

    //make alterations to the table without effecting the data.
function constructTable(){
    minus_counter = 0;
    
    //rebuild    
    while(table.hasChildNodes()) table.removeChild(table.lastChild);
        counter = 0;
        interval_counter = global_data[current_page].length - 1;
        construct_interval = setInterval(function(){
            for(var i = 0 ; i < 100; i++)
                buildFullTableInterval();
        },25);
}

function buildEntryCounter(board_refine){
	var temp_counter = counter;
	if(current_page - max_page != 0) temp_counter += page_load_amount * (actual_page - 2) + max_counter % page_load_amount;
	//current
	var rhs = Math.floor(temp_counter / 1000) + "";
	var lhs = temp_counter % 1000 + "";
	while(lhs.length < 3) lhs = "0" + lhs;
	var current;
	if(rhs == 0) current = lhs;
	else current = rhs + "," + lhs;
	//max
	var rhs_max =  Math.floor(max_counter / 1000)+ "";
	var lhs_max = max_counter % 1000 + "";
	while(lhs_max.length < 3) lhs_max = "0" + lhs_max;
	var max;
	if(rhs_max == 0) max = lhs_max;
	else max = rhs_max + "," + lhs_max;
	//min(maybe for use later?)
	temp_counter -= page_load_amount * (actual_page) + max_counter % page_load_amount;
	var rhs_min = Math.floor(temp_counter / 1000);
	var lhs_min = temp_counter % 1000 + "";
	while(lhs_min.length < 3) lhs_min  = "0" + lhs_min;
	var min;
	if(rhs_min == 0) min = lhs_min;
	else min = rhs_min + "," + lhs_min;

	count.innerHTML = "Displaying <span class='font-weight-bold px-1 py0'> " + current +  " </span> of <span class='font-italic px-1 py0'> " + max +  " </span> results";

	if(counter == 0 ){
		document.getElementById("bansTable").innerHTML = "<tr><td colspan=8>No Results Found on search" + "" + "...</td></tr>";
	}
}

function buildPages(){
    var page_tables = document.getElementsByClassName("page_table");
    var page_limit = parseInt(max_page) + 1;
    window_width = window.innerWidth;
    for(var table_number = 0; table_number < 2 ; table_number++){
        //clear for rebuild
        while(page_tables[table_number].hasChildNodes()){
            page_tables[table_number].removeChild(page_tables[table_number].firstChild);
        }
        var top_table = !top_table;
        for(var page = 1; page <=  page_limit; page++){
            var entry = document.createElement("DIV");
			entry.setAttribute("onmouseover", 'this.style.backgroundColor=\"rgb(250,250,255)\"');
			entry.setAttribute("onmouseout", 'this.style.backgroundColor=\"white\"');
            entry.setAttribute("style","min-width:50px;background-color:white");
			entry.setAttribute("class","text-center border  rounded view overlay");
            var entry_link = document.createElement("SPAN");
            var fontSize = "font-size:18px;"
			//creation for upper page list
            if(top_table){
                if(page == actual_page){
                    entry_link.innerHTML = "<a href = \"javascript:void(0)\" style='color:red;" + fontSize +"' >" + page + "</a>";
                }
                else{
                    entry_link.innerHTML = "<a href = \"javascript:void(0)\" style='" + fontSize +"'>" + page + "</a>";
                }
            }
			//creation for lower page list
            else {
                if(page == actual_page){
                    entry_link.innerHTML = "<a href = \"javascript:void(0)\" style='color:red'>" + page + "</a>";
                }
                else{
                    entry_link.innerHTML = "<a href = \"javascript:void(0)\">" + page + "</a>";
                }
            }
			//click logic
            (function (_page){ 
                entry_link.addEventListener("click", function(){
                    if(actual_page != _page){
                        current_page = max_page - (_page - 1);
                        actual_page = _page;
                        table.innerHTML = "<tr><td colspan=8>...Loading New Entries - page " + _page + " ...</td></tr>";
                        //reloads page from index onhashchange
						    //reloads page from index onhashchange
						board_refine = document.getElementById("refinement").value;
						comment_refine = encodeURIComponent(document.getElementById("commentrefine").value);
						rule_refine = document.getElementById("rulerefine").value;
						window.location.hash = '#' + String(_page) + "|" + board_refine + "|" + comment_refine + "|" + rule_refine;
                        document.getElementById("pages").innerHTML = "Page " + actual_page;
                    }
                });
            })(page);
            document.getElementById("pages").innerHTML = "Page " + actual_page;
            entry.appendChild(entry_link);
            page_tables[table_number].appendChild(entry);        
        }
    }
}
