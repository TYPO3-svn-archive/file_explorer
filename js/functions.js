function fileexplorer_cMenu(id, pageId, type, title, view, ro) {
    $('#cMenuItem_' + id).contextMenu('#cMenu_' + type, {
        menuStyle: {
            border: "1px solid #000",
            opacity: "0.95",
            backgroundColor: "#d9ebf5"
        },
        itemStyle: {
            fontSize: "12px",
            opacity: "1",
            fontFamily: "arial, sans-serif",
            backgroundColor: "#EEE",
            color: "black",
            /*border : "1px solid #000",*/
            padding: "0px"
        },
        itemHoverStyle: {
            color: "black",
            backgroundColor: "#d9ebf5",
            border: "1px solid #a8d8eb"
        },
        bindings: {
            "#editFolder": function (t) {
                var tmpHref = 'index.php?id=' + pageId + '&amp;type=769&amp;tx_fileexplorer_pi1[popup]=1&amp;tx_fileexplorer_pi1[action]=edit_folder&amp;tx_fileexplorer_pi1[id]=' + id + '&amp;height=300&amp;width=300&amp;keepThis=true&amp;TB_iframe=true';
                tb_show('Edit Folder', tmpHref, false);
            },
            "#editFile": function (t) {
                var tmpHref = 'index.php?id=' + pageId + '&amp;type=769&amp;tx_fileexplorer_pi1[popup]=1&amp;tx_fileexplorer_pi1[action]=edit_file&amp;tx_fileexplorer_pi1[id]=' + id + '&amp;height=300&amp;width=300&amp;keepThis=true&amp;TB_iframe=true';
                tb_show('Edit File', tmpHref, false);
            },
            "#saveFolder": function (t) {
                window.location.href = 'index.php?eID=tx_fileexplorer_pi1&action=download_folder&id=' + id;
            },
            "#saveFile": function (t) {
                window.location.href = 'index.php?eID=tx_fileexplorer_pi1&action=download_file&id=' + id;
            },
            "#viewFolder": function (t) {},
            "#browseFolder": function (t) {
                window.location.href = 'index.php?id=' + pageId + '&tx_fileexplorer_pi1[folder]=' + id + '&tx_fileexplorer_pi1[view]=' + view;
            },

            "#viewFile": function (t) {
                var tmpHref = 'index.php?id=' + pageId + '&type=769&tx_fileexplorer_pi1[popup]=1&tx_fileexplorer_pi1[view]=detail&tx_fileexplorer_pi1[id]=' + id + '&height=550&amp;width=600';
                tb_show(title, tmpHref, false);
            },
            "#deleteFolder": function (t) {
                Check = confirm("###FOLDERDELCONFIRM###" + title + "\"");
                if (Check == true) {
                    $.get('index.php?eID=tx_fileexplorer_pi1&action=delete_folder&id=' + id,
                    function (result) {
                        if (result !== '') {
                            alert(result);
                        } else {
                            $('#feFolder_' + id).remove();
                        }
                    });
                }
            },
            "#deleteFile": function (t) {
                Check = confirm("###FILEDELCONFIRM###" + title + "\"");
                if (Check == true) {
                    $.get('index.php?eID=tx_fileexplorer_pi1&action=delete_file&id=' + id,
                    function (result) {
                        if (result !== '') {
                            alert(result);
                        } else {
                            $('#feFile_' + id).remove();
                        }
                    });
                }
            }
        }
    }, ro);
}