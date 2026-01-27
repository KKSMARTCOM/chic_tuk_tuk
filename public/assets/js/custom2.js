(function ($, window) {
    "use strict";

    window.showAlert = function (type, message) {
        const colors = {
            success: {
                bg: "bg-emerald-50",
                border: "border-emerald-600",
                text: "text-emerald-800",
                title: "Succès",
            },
            error: {
                bg: "bg-red-50",
                border: "border-red-600",
                text: "text-red-800",
                title: "Erreur",
            },
            warning: {
                bg: "bg-yellow-50",
                border: "border-yellow-600",
                text: "text-yellow-800",
                title: "Attention",
            },
            info: {
                bg: "bg-blue-50",
                border: "border-blue-600",
                text: "text-blue-800",
                title: "Info",
            },
        };

        const c = colors[type] || colors.info;

        const html = `
            <div class="global-alert w-full max-w-2xl mb-4 rounded-lg ${c.bg} border-l-4 ${c.border} ${c.text} p-4 flex items-start justify-between z-10" role="alert">
                <div>
                    <strong class="font-semibold">${c.title}</strong>
                    <div class="mt-1 text-sm">${message}</div>
                </div>
                <button type="button" class="alert-close ml-4 ${c.text} hover:opacity-80">&times;</button>
            </div>
        `;

        const $el = $(html);

        $("#js-alert-container").append($el);

        // Auto-hide
        setTimeout(function () {
            $el.slideUp(300, function () {
                $(this).remove();
            });
        }, 6000);
    };
})(jQuery, window);
