import Chart from "chart.js/auto";

export function MakeChart(chart){
    window.chart = chart;

    if(window.chart !== null && window.chart !== undefined){
        const config = {
            type: window.chart.config.type,
            data: window.chart.data,
            options: window.chart.config.options,
        };
        new Chart(document.getElementById(window.chart.config.chartId), config);
    }

}
