#!/bin/bash

# Check if at least one file is provided
if [ "$#" -lt 1 ]; then
    echo "Usage: $0 file1 [file2 ... fileN]"
    exit 1
fi

# Define the output archive names
TIMESTAMP=$(date +"%Y%m%d%H%M%S")
TAR_ARCHIVE="blockchain-explorer-$TIMESTAMP.tar"
ZIP_ARCHIVE="blockchain-explorer-$TIMESTAMP.zip"

# Create a tar archive
echo "Creating $TAR_ARCHIVE..."
tar -cvf "$TAR_ARCHIVE" "$@"
if [ $? -eq 0 ]; then
    echo "Successfully created $TAR_ARCHIVE"
else
    echo "Failed to create $TAR_ARCHIVE"
    exit 1
fi

# Create a zip archive
echo "Creating $ZIP_ARCHIVE..."
zip "$ZIP_ARCHIVE" "$@"
if [ $? -eq 0 ]; then
    echo "Successfully created $ZIP_ARCHIVE"
else
    echo "Failed to create $ZIP_ARCHIVE"
    exit 1
fi

echo "Archives created successfully:"
echo "  - $TAR_ARCHIVE"
echo "  - $ZIP_ARCHIVE"
