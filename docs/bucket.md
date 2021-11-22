# Bucket

A bucket abstracts a data (file) storage and provides the following methods:

|Method  |Description|
|--------|-----------|
filePush |push (put) a local file to a remote location in the bucket
filePull |pull (retrieve) a remote file in a bucket to the local machine
fileAvailable|checks if a given path/file is available (exists) in the bucket (and if it is a file)
fileDelete|removes a file in a bucket at the given path
fileMove|moves a remote file to another location in the bucket
fileGetInfo|retrieves more information (metadata) about a file in the bucket
dirList|retrieves available directories in the bucket at a given location (bucket root by default)
dirAvailable|checks whether a directory exists at a given location (and if it is a directory)
isFile|checks whether a given location/path is a file or not

The experience using buckets is highly oriented at using S3 buckets. Directories are created implicitly by pushing a file to a specific location.
The generic `bucketInterface` does not assume any specific technology behind a given bucket, but makes sure you can use differing platforms in differing runtime environments transparently.

At the time of writing, the following bucket drivers are available:

|Driver|Description|
|------|-----------|
local|Local filesystem bucket (directory)
ftp|FTP(S) connection
sftp|SFTP connection
s3|S3 Bucket Client

Every driver is capable of abstracting directories and access (similar to `chroot`) by passing configuration keys like `basedir`.
All methods (like `->filePull(...)`) adhere to this and act relative to the current basedir as a 'root' directory.
