#!/bin/sh
#==========
# Unix shell script to manually process an APOD image
#
# Sometimes an APOD image is not properly being processed
# by the cron-job, resulting in a broken / non-existent
# image file for that day's APOD on zorg.
# This script contains all steps required to help fixing
# a missing daily APOD image, by downloading the image
# to a temporary folder and then resizing it into two
# different files: a "pic_nnnnn.jpg" and "tn_nnnnn.jpg" so
# as moving those optimized image files to the APOD gallery.
# 
# Usage via command line on xoli using a ssh shell
# ---
# - must be run via "sudo" for proper permissions!
# - 2 parameters are required:
#   1) Gallery Pic Image-ID
#   2) APOD Image-URL (wrapped in quotes "")
# 
# Example:
# $ sudo /usr/local/bin/apod/apod_download_image.sh [galleryImageID] 'https://apod.nasa.gov/apod/image/APOD_IMAGE_ID/APOD_IMAGE_NAME.jpg'
#
# @author IneX
# @package zorg
# @subpackage Scripts
# @version 1.0
# @since 1.0 <inex> 14.06.2019 Script added
#==========

# Input parameter validation
if [ -z "$1" ]; then
  printf "[WARN] Missing input 1 the Gallery Image-ID. Use:\nscript.sh galleryImageID 'https://apod.nasa.gov/apod/image/image_id/image_name.jpg'\n"
  exit 2
else
  gallery_picid_input="$1"
fi
if [ -z "$2" ]; then
  printf "[WARN] Missing input 2 with APOD URL. Use:\nscript.sh galleryImageID 'https://apod.nasa.gov/apod/image/image_id/image_name.jpg'\n"
  exit 2
else
  apod_url_input="$2"
fi

# Define variables
apod_download_url=$apod_url_input
pic_id=$gallery_picid_input
tmp_dirpath="/var/data/temp/"
apod_gallery_dir="/var/data/gallery/41/"
file_extension="jpg"
gallery_pic="pic_$pic_id"
gallery_tn="tn_$pic_id"

# Download APOD image from URL
tmp_filename=$pic_id.$file_extension
printf "Downloading $apod_download_url usign curl:\n"
curl "$apod_download_url" -o $tmp_dirpath$tmp_filename
printf "File saved to $tmp_dirpath$tmp_filename\n"

# Convert downloaded APOD image to correct sizes
printf "Converting image...\n"
convert $tmp_dirpath$tmp_filename -resize 800 $tmp_dirpath$gallery_pic.$file_extension
printf "Pic created: $tmp_dirpath$gallery_pic.$file_extension\n"
convert $tmp_dirpath$tmp_filename -resize 150 $tmp_dirpath$gallery_tn.$file_extension
printf "Thumb created: $tmp_dirpath$gallery_tn.$file_extension\n"

# Move APOD image and thumbnail to APOD-Gallery directory
printf "Moving images to gallery: $apod_gallery_dir\n"
mv $tmp_dirpath$gallery_pic.$file_extension $apod_gallery_dir
mv $tmp_dirpath$gallery_tn.$file_extension $apod_gallery_dir

# Remove temporary downloaded Image file (sourcefile)
printf "Cleanup. Removing temporary downloaded file: $tmp_dirpath$tmp_filename\n"
rm $tmp_dirpath$tmp_filename

# Done
printf "DONE! Check it out:\nhttp://zorg.ch/gallery/$pic_id\n"
