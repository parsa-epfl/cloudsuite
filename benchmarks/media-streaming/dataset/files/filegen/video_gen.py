import re
from subprocess import call
import os
from sys import argv
from random import randint

video_io_filenames ={}
config_param_path = None
video_file_info_path = None
textpaths_dir = None
output_videos_dir = None
file_resolution_info = None
videos_path = None
videos_js_path = None
def bytes_to_MB(number_of_bytes):
    factor = 1024*1024
    number_of_bytes = float(number_of_bytes)
    number_of_bytes /= factor
    precision = 1
    number_of_bytes = round(number_of_bytes, precision)
    return number_of_bytes

def generate_video_file_with_requested_size(requested_video_size_in_bytes,input_file_path,resolution,output_video_name):
    file_stats = os.stat(input_file_path)
    actual_file_size_bytes = int(file_stats.st_size)
    size_diff = int(bytes_to_MB(actual_file_size_bytes)) - int(bytes_to_MB(requested_video_size_in_bytes))

    if size_diff < 0:
        num_concatenations = int(requested_video_size_in_bytes / actual_file_size_bytes) + 1
    else:
        num_concatenations = 1
    output_file_name = os.path.splitext(output_video_name)[0]
    in_txt_file_path = textpaths_dir + output_file_name + ".txt"
    out_mp4_file_path = output_videos_dir + str(output_video_name)
    video_io_filenames[in_txt_file_path] = out_mp4_file_path
    input_file_path = input_file_path
    in_file = open(in_txt_file_path, "a+")
    for num in range(int(num_concatenations)):
        in_file.write("file " + "'" + input_file_path + "'\n")

def get_resolution():
    f = open(file_resolution_info, 'r')
    resolution = None
    for line in f:
        if "video_quality" in line:
            resolution = line.split("=")[1]
    f.close()
    resolution = resolution.strip()
    return resolution

def getopts(argv):
    opts = {}
    while argv:
        if argv[0][0] == '-':
            opts[argv[0]] = argv[1]
        argv = argv[1:]
    return opts

def get_video_info():
    video_request_dict = {}
    f = open(video_file_info_path, 'r')
    video_names_list = []
    for line in f:
        line = re.sub('[ \t]+', ' ', line)
        if line[0] != "#":
            video_info = line.split(" ")
            size = int(video_info[1])
            video_request_dict[video_info[0]] = size
            video_names_list.append(video_info[0])
    f.close()
    return video_request_dict,video_names_list

def parse_videos_info(resolution,videos_path):
    input_video_collection = []
    complete_path = videos_path+"/"+resolution+"/"
    for file in os.listdir(complete_path):
        if file.endswith(".mp4"):
           input_video_collection.append(os.path.join(complete_path, file))
    return input_video_collection

if __name__ == '__main__':
    myargs = getopts(argv)

    if '-p' not in myargs or '-v' not in myargs or '-s' not in myargs or '-o' not in myargs:
        raise ValueError('Please provide a valid config files.')
        exit(1)
    else:
        file_resolution_info = myargs["-p"]
        video_file_info_path = myargs["-v"]
        videos_path = myargs["-s"]
        output_videos_dir = myargs["-o"]


    textpaths_dir = "/tmp/textpaths/"
    videos_js_path = output_videos_dir+"/"+"test_videos.js"
    resolution = get_resolution()
    if resolution is None:
        raise ValueError('Please provide a valid config param file.')
        exit(1)

    input_video_collection = parse_videos_info(resolution,videos_path)

    video_request_dict,video_names_list = get_video_info()
    videos_js_file = open(videos_js_path,"a+")
    videos_list_in_js = ""
    
    for key in video_names_list:
        output_video_name = "full-"+resolution+"-"+key+".mp4"
        requested_video_size_in_bytes = video_request_dict[key]
        local_file_path = input_video_collection[0]
        del input_video_collection[0]
        input_video_collection.append(local_file_path)    
        generate_video_file_with_requested_size(requested_video_size_in_bytes,local_file_path,resolution,output_video_name)
        videos_list_in_js = videos_list_in_js+'"'+output_video_name+'",'
    videos_js_file.write("var videos"+resolution+" = [" +videos_list_in_js[:-1]+"]\n")


# Execute ffmpeg to concatenate these input videos to get output videos of required sizes
for in_txt_filename in video_io_filenames.keys():
    ffmpeg_cmd = "ffmpeg -y -loglevel error -f concat -safe 0 -i " + in_txt_filename + " -c copy " + video_io_filenames.get(in_txt_filename);
    call(ffmpeg_cmd, shell=True)

