function initSelectAjax(selector,searchUrl,model,field){
    $(selector).select2("destroy").select2().select2({
        placeholder: "Поиск ...",
        ajax: {
            url: searchUrl,
            dataType: "json",
            method: "POST",
            delay: 250,
            data: function(e) {
                return {
                    q: e.term,
                    page: e.page,
                    model: model,
                    field: field
                }
            },
            processResults: function(e, t) {
                return t.page = t.page || 1,
                    $.each(e, function(e, t) {
                        t.desc = t.name
                    }),
                {
                    results: e
                }
            },
            cache: !0
        },
        escapeMarkup: function(e) {
            return e
        },
        templateResult: function(e){
            if (e.custom_name)
                return e.custom_name;
            var t = "<div class='select2-result-repository clearfix'><div class='select2-result-repository__meta'><div class='select2-result-repository__title'>" + e.tag_name + "</div>";
            return t
        },
        templateSelection: function(e){
            return e.custom_name || e.tag_name || e.text
        }
    });
}
