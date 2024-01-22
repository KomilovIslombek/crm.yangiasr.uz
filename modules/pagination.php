<?php
function pagination($page_count, $href, $count) {
    if (gettype($count) == "double") $count = (int)($count + 1);

    // qurl
    unset($_REQUEST["page"]);
    $qurl = http_build_query($_REQUEST);

    global $page;

    $output = '';
    if (!isset($page)) $page = 1;
    if ($count != 0) $pages = ceil($page_count / $count);
    // if ($pages == 1 || $pages == 0) return;

    if ($page != 1){
        $output .= '<li class="page-item page-indicator">
                        <a class="page-link" href="'.$href.'?page='.($page-1).'">
                            <i class="la la-angle-left"></i></a>
                    </li>';
    } else {
        $output .= '<li class="page-item page-indicator" style="cursor:no-drop">
                        <a class="page-link" href="javascript:void(0);" style="cursor:no-drop">
                            <i class="la la-angle-left"></i></a>
                    </li>';
    }
    
    //if pages exists after loop's lower limit
    if ($pages > 1) {
        if (($page - 3) > 0) {
            $output .= '<li class="page-item">
                            <a href="'.$href.'?page=1'.($qurl ? "&".$qurl : "").'" class="page-link">1</a>
                        </li>';
        }
        if (($page - 3) > 1) {
            $output .= '<li class="page-item">
                            <a href="javascript:void(0);" class="page-link">...</a>
                        </li>';
        }
        
        //Loop for provides links for 2 pages before and after selected page
        for ($i = ($page - 2); $i<=($page + 2); $i++)	{
            if ($i < 1) continue;
            if ($i > $pages) break;
            if ($page == $i) {
                $output .= '<li class="page-item active">
                                <a href="javascript:void(0);" class="page-link">'.$i.'</a>
                            </li>';
            } else {
                $output .= '<li class="page-item">
                                <a href="'.$href.'?page='.$i.($qurl ? "&".$qurl : "").'" class="page-link">'.$i.'</a>
                            </li>';
            }
        }
        
        //if pages exists after loop's upper limit
        if (($pages-($page + 2)) > 1) {
            $output .= '<li class="page-item">
                           <a class="page-link">...</a>
                        </li>';
        }
        if (($pages-($page + 2)) > 0) {
            $output .= '<li class="page-item '.($page == $pages ? "active" : "").'">
                            <a href="'.$href.'?page='.$pages.($qurl ? "&".$qurl : "").'" class="page-link">'.$pages.'</a>
                        </li>';
        }
    }

    if ($page != $pages){
        $output .= '<li class="page-item page-indicator">
                        <a href="'.$href.'?page='.($page+1).'" class="page-link">
                            <i class="la la-angle-right"></i>
                        </a>
                    </li>';
    } else {
        $output .= '<li class="page-item page-indicator" style="cursor:no-drop">
                        <a href="javascript:void(0);" class="page-link" style="cursor:no-drop">
                            <i class="la la-angle-right"></i>
                        </a>
                    </li>';
    }

    $form_arr = [];
    if ($qurl) parse_str($qurl, $form_arr);
    $additional_forms = '';
    foreach ($form_arr as $form_name => $form_val) {
        $additional_forms .= '<input type="hidden" name="'.$form_name.'" value="'.$form_val.'">';
    }
    
    $output .= '<form action="" method="GET" style="margin-left:50px">
                    <div class="form-row">
                        '.($additional_forms ? $additional_forms : "").'
                        <div class="form-group col-md-6">
                            <input type="number" name="page" min="1" max="'.$pages.'" class="form-control text-center" style="height:45px;" placeholder="'.$page.'">
                        </div>
                        <div class="form-group col-md-6">
                            <button type="submit" class="btn btn-primary form-control" style="height:45px;">&raquo;</button>
                        </div>
                    </div>
                </form>';

    $pagination_start = '<';
    $pagination_end = '';

    return '<nav id="pagination-wrapper" style="'.($pages == 1 || $pages == 0 ? 'display:none' : '').'">
        <ul class="pagination mt-4 justify-content-end">
            '.$output.'
        </ul>
    </nav>';
}
?>