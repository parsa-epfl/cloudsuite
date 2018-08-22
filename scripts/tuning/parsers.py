import re
import statistics


def parse_data_caching(log_file_names):
    graph_data = {
        'graphs': [],
        'dimensions': (len(log_file_names), 1)
    }
    for log_file_name in log_file_names:
        log_file = open(log_file_name)

        profiles = {}
        current_rps = None

        for line in log_file:
            line = line.replace('\n', '')

            if line.startswith('rps: '):
                current_rps = int(line.split()[1])
                # if current_rps == 105000:
                #     break
                profiles[current_rps] = {
                    'cpu_util': {},
                    '95th': [],
                    '99th': [],
                    'rps': [],
                }

            if line.startswith('cpu_start: '):
                cpu_start = int(line.split()[1])

            if line.startswith('cpu_count: '):
                cpu_count = int(line.split()[1])

            if current_rps is not None:
                match = re.match(
                    r"^\d\d:\d\d:\d\d[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+"
                    r"([\d\.])+[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)$",
                    line
                )
                if match is not None:
                    cpu_stats = match.groups()
                    if int(cpu_stats[0]) not in profiles[current_rps]['cpu_util']:
                        profiles[current_rps]['cpu_util'][int(cpu_stats[0])] = []
                    profiles[current_rps]['cpu_util'][int(cpu_stats[0])].append(100 - float(cpu_stats[10]))

                match = re.match(
                    r"^[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),"
                    r"[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),"
                    r"[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+)$",
                    line
                )
                if match is not None:
                    load_stat = match.groups()
                    profiles[current_rps]['95th'].append(float(load_stat[9]))
                    profiles[current_rps]['99th'].append(float(load_stat[10]))
                    profiles[current_rps]['rps'].append(float(load_stat[1]))

        # form the graph data to plot
        def mean_and_error_last_numbers(numbers, count=20, offset=0):
            selected_numbers = []
            for index in range(count):
                number = numbers[len(numbers) - 1 - index - offset]
                selected_numbers.append(number)
            return statistics.mean(selected_numbers), statistics.stdev(selected_numbers)

        def merge_cpu_set_stat(all_utilizations, start, count):
            result_utilizations = []
            for sample_index in range(len(all_utilizations[0])):
                cpu_set_utilizations = []
                for cpu_index in range(start, start + count):
                    cpu_set_utilizations.append(all_utilizations[cpu_index][sample_index])
                result_utilizations.append(statistics.mean(cpu_set_utilizations))
            return result_utilizations

        x_data = [mean_and_error_last_numbers(profiles[key]['rps'])[0] for key in profiles.keys()]

        utilizations = []
        utilization_errors = []

        tail_latencies_95th = []
        tail_latency_95th_errors = []

        tail_latencies_99th = []
        tail_latency_99th_errors = []

        for rps in profiles.keys():
            utilization, utilization_error = mean_and_error_last_numbers(
                merge_cpu_set_stat(profiles[rps]['cpu_util'], cpu_start, cpu_count),
                2
            )
            utilizations.append(utilization)
            utilization_errors.append(utilization_error)

            tail_latency_95th, tail_latency_95th_error = mean_and_error_last_numbers(profiles[rps]['95th'])
            tail_latencies_95th.append(tail_latency_95th)
            tail_latency_95th_errors.append(tail_latency_95th_error)

            tail_latency_99th, tail_latency_99th_error = mean_and_error_last_numbers(profiles[rps]['99th'])
            tail_latencies_99th.append(tail_latency_99th)
            tail_latency_99th_errors.append(tail_latency_99th_error)

        # noinspection PyTypeChecker
        graph_data['graphs'].append(
            {
                'x': x_data,
                'x_label': 'Request per Second',
                'title': 'Data Caching (memcached) with {} Core(s)'.format(cpu_count),
                'y': {
                    'left': [
                        {
                            'type': 'plot',
                            'format': 'b.',
                            'data': utilizations,
                            'error': utilization_errors,
                            'label': 'Server CPU Utilization (%)',
                        }
                    ],
                    'left_label': 'Server CPU Utilization (%)',
                    'left_limit': None,
                    'right': [
                        {
                            'type': 'errorbar',
                            'data': tail_latencies_95th,
                            'error': tail_latency_95th_errors,
                            'label': '95th Latency (ms)',
                        },
                        # {
                        #     'type': 'errorbar',
                        #     'data': tail_latencies_99th,
                        #     'error': tail_latency_99th_errors,
                        #     'label': '99th Latency (ms)',
                        # },
                    ],
                    'right_limit': 90,
                    'right_label': 'Latency (ms)'
                }
            }
        )

    return graph_data
