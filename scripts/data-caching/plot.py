import re
import sys
from matplotlib import pyplot

profiles = {}
current_rps = None

for line in sys.stdin:
    line = line.replace('\n', '')
    print(line)
    if line.startswith('rps: '):
        current_rps = int(line.split()[1])
        if current_rps == 98000:
            break
        profiles[current_rps] = {
            'cpu_util': {},
            '95th': [],
            'rps': [],
        }

    if current_rps is not None:
        match = re.match(
            r"^\d\d:\d\d:\d\d[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)[ \t]+([\d\.]+)$",
            line
        )
        if match is not None:
            cpu_stats = match.groups()
            if int(cpu_stats[0]) not in profiles[current_rps]['cpu_util']:
                profiles[current_rps]['cpu_util'][int(cpu_stats[0])] = []
            profiles[current_rps]['cpu_util'][int(cpu_stats[0])].append(100 - float(cpu_stats[10]))

        match = re.match(
            r"^[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+),[ \t]+([\d\.]+)$",
            line
        )
        if match is not None:
            load_stat = match.groups()
            profiles[current_rps]['95th'].append(float(load_stat[9]))
            profiles[current_rps]['rps'].append(float(load_stat[1]))

utilizations = []


def average_tail(numbers, count=1, offset=0):
    sum = 0.0
    for i in range(count):
        sum += numbers[len(numbers) - 1 - i - offset]
    return sum / count


for key in profiles.keys():
    average_usage = 0.0
    for i in range(4):
        average_usage += (
                                 average_tail(profiles[key]['cpu_util'][i]) +
                                 average_tail(profiles[key]['cpu_util'][i + 4])
                         ) / 8
    utilizations.append(average_usage)

fig, utilization_plot = pyplot.subplots()

utilization_plot.plot(profiles.keys(), utilizations, 'b-')
utilization_plot.set_ylabel('CPU Utilization (%)', color='b')
utilization_plot.set_xlabel('Requests Per Second')
utilization_plot.tick_params('y', colors='b')

latency_plot = utilization_plot.twinx()
latency_plot.plot(profiles.keys(), [average_tail(profiles[key]['95th']) for key in profiles.keys()], 'r-')
latency_plot.set_ylabel('95th latency (ms)', color='r')
latency_plot.tick_params('y', colors='r')
fig.tight_layout()
pyplot.show()
