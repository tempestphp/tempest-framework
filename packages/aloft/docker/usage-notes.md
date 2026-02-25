# Usage notes

NB: This file is NOT FINAL and will be removed from the PR this is just so that the image can be tested in development

## Build locally

cd into the folder
```bash
docker buildx bake
```
After build you should have something similar, disk usage will vary slightly based on your arch (this is aarch64 on macos m1 17.x).
```bash
docker % docker image ls

IMAGE                                              ID             DISK USAGE   CONTENT SIZE   EXTRA
gcr.io/distroless/cc-debian13:debug-nonroot        f60c5a64690d       38.5MB             0B        
gcr.io/distroless/cc-debian13:nonroot              5c5da034ed6e       37.2MB             0B        
tempestphp/aloft:1.11.2-8.5.3-debug-nonroot        1bf5840bddb2        219MB             0B        
tempestphp/aloft:1.11.2-8.5.3-nonroot              a5039ddf9345        218MB             0B       
tempestphp/aloft:debug-nonroot                     1bf5840bddb2        219MB             0B        
tempestphp/aloft:latest-nonroot                    a5039ddf9345        218MB             0B     
```

## Push to a registry

cd into the folder
```bash
PUSH=1 REGISTRY=registry.url/tempestphp docker buildx bake
```

View on your registry, but should create the four tags and two images.

e.g. I pushed to my private gitea

tempestphp/aloft/versions:

1.11.2-8.5.3-debug-nonroot
Published 16 hours ago by iamdadmin

latest-nonroot
Published 16 hours ago by iamdadmin

1.11.2-8.5.3-nonroot
Published 16 hours ago by iamdadmin

debug-nonroot
Published 16 hours ago by iamdadmin
