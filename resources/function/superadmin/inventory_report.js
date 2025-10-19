$(document).ready(function () {
    const table = $("#inventoryReport").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: {
                    placeholder: "Search here",
                },
            },
        },
        aLengthMenu: [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"],
        ],
        iDisplayLength: 10,
        language: {
            search: "Search: ",
        },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/inventory-report",
        autoWidth: false,
        columns: [
            { data: "sku", name: "sku", width: "10%" },
            { data: "name", name: "name", width: "20%" },
            // {
            //     data: "created_at",
            //     name: "created_at",
            //     width: "15%",
            //     render: function (data) {
            //         return new Date(data).toLocaleString();
            //     },
            // },
            {
                data: "price",
                name: "price",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            { data: "stockIn", name: "stockIn", width: "10%" },
            { data: "stockOut", name: "stockOut", width: "10%" },
            { data: "current_stock", name: "current_stock", width: "10%" },
            {
                data: null,
                name: "inventory_breakdown",
                orderable: false,
                searchable: false,
                width: "30%",
                render: function (data, type, row, meta) {
                    const chartId = `chart-${meta.row}`;

                    if (
                        !row.inventory_breakdown ||
                        row.inventory_breakdown.length === 0
                    ) {
                        return `<span class="text-muted">Oops! No chart data right now.</span>`;
                    }

                    setTimeout(() => {
                        renderPieChart(chartId, row.inventory_breakdown);
                    }, 100);

                    return `<canvas id="${chartId}" height="100"></canvas>`;
                },
            },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    function renderPieChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(ctx, {
            type: "pie",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Inventory Breakdown",
                        data: values,
                        backgroundColor: [
                            "#4caf50",
                            "#f44336",
                            "#2196f3",
                            "#ff9800",
                            "#9c27b0",
                            "#607d8b",
                        ],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                    },
                },
            },
        });
    }
});
