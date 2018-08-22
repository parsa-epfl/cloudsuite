import sys

from matplotlib import pyplot, rc
import parsers


def plot_graphs(graph_data, output_file_name):
    """
    This function plots the parsed graph from logs.
    :param output_file_name: this parameter is the path to output image file
    :param graph_data: the data should be in a format like the following:
    graph_data = {
        'graphs': [
                {
                    'x': x_data,
                    'x_label': 'Requests Per Second',
                    'y': {
                        'left': [
                            {
                                'type': 'plot',
                                'format': 'b.',
                                'data': utilizations,
                                'error': utilization_errors,
                                'label': 'Server CPU Utilization',
                            }
                        ],
                        'left_label': 'Server CPU Utilization (%)',
                        'right': [
                            {
                                'type': 'errorbar',
                                'data': tail_latencies_95th,
                                'error': tail_latency_95th_errors,
                                'label': '95th Latency (ms)',
                            },
                            {
                                'type': 'errorbar',
                                'data': tail_latencies_99th,
                                'error': tail_latency_99th_errors,
                                'label': '99th Latency (ms)',
                            },
                        ],
                        'right_label': 'Latency (ms)'
                    }
                }
            ],
        'dimensions': (1, 1)
    }
    """
    rc('font', size=24)
    pyplot.figure(figsize=(20, 30))

    graph_counter = 1
    last_sub_plot = None

    for single_graph_data in graph_data['graphs']:
        left_plot = pyplot.subplot(
            graph_data['dimensions'][0],
            graph_data['dimensions'][1],
            graph_counter,
            sharex=last_sub_plot
        )
        if single_graph_data['y']['left_limit'] is not None:
            left_plot.set_ylim(bottom=0, top=single_graph_data['y']['left_limit'])
        left_plot.set_title(single_graph_data['title'])
        last_sub_plot = left_plot
        plots = []

        for single_left_graph in single_graph_data['y']['left']:
            if single_left_graph['type'] == 'plot':
                plots.extend(left_plot.plot(
                    single_graph_data['x'], single_left_graph['data'], single_left_graph['format'],
                    label=single_left_graph['label']
                ))
            elif single_left_graph['type'] == 'errorbar':
                plots.append(left_plot.errorbar(
                    single_graph_data['x'], single_left_graph['data'], single_left_graph['error'],
                    linestyle='None', marker='.', capsize=3, label=single_left_graph['label']
                ))

            left_plot.tick_params('y')

        left_plot.set_ylabel(single_graph_data['y']['left_label'])
        left_plot.set_xlabel(single_graph_data['x_label'])

        right_plot = left_plot.twinx()
        if single_graph_data['y']['right_limit'] is not None:
            right_plot.set_ylim(bottom=0, top=single_graph_data['y']['right_limit'])
        for single_right_graph in single_graph_data['y']['right']:
            if single_right_graph['type'] == 'plot':
                plots.extend(right_plot.plot(
                    single_graph_data['x'], single_right_graph['data'], single_right_graph['format'],
                    label=single_right_graph['label']
                ))
            elif single_right_graph['type'] == 'errorbar':
                plots.append(right_plot.errorbar(
                    single_graph_data['x'], single_right_graph['data'], single_right_graph['error'],
                    linestyle='None', marker='.', capsize=3, label=single_right_graph['label']
                ))

            right_plot.tick_params('y')

        right_plot.set_ylabel(single_graph_data['y']['right_label'])
        left_plot.legend(plots, [plot.get_label() for plot in plots], loc=0)

        graph_counter += 1

    pyplot.tight_layout()
    pyplot.savefig(output_file_name)


if len(sys.argv) < 3:
    print('Usage: python plotter.py [benchmark] [output_file_name] [file_names...]', file=sys.stderr)
    exit(1)

try:
    parse = getattr(parsers, 'parse_' + sys.argv[1])
    plot_graphs(parse(sys.argv[3:]), sys.argv[2])
except AttributeError:
    print('Undefined benchmark "{}".'.format(sys.argv[1]), file=sys.stderr)
except Exception as error:
    print(error, file=sys.stderr)
