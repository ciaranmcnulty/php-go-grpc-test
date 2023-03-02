package main

import (
    "context"
    "fmt"
	"log"
	"net"

	"google.golang.org/grpc"
	pb "github.com/packyderm/packyderm/service"
)

type ImageBuilderServer struct {
	pb.UnimplementedImageBuilderServer
}

func (s *ImageBuilderServer) FetchConfigFile (ctx context.Context, req *pb.ConfigFileReq) (*pb.ConfigFileRes, error) {
    log.Printf("Received Config file request")

    return &pb.ConfigFileRes{Contents: "{}"}, nil
}

func (s *ImageBuilderServer) BuildImage (ctx context.Context, req *pb.BuildImageReq) (*pb.BuildImageRes, error) {
    log.Printf("Received Build image request with Dockerfile:\n%v", req.Dockerfile)

    return &pb.BuildImageRes{}, nil
}

func main() {
    log.Printf("Starting server");
	listener, err := net.Listen("tcp", fmt.Sprintf(":%d", 3229))
	if err != nil {
		log.Fatalf("failed to listen: %v", err)
	}
	s := grpc.NewServer()
	pb.RegisterImageBuilderServer(s, &ImageBuilderServer{})

	log.Printf("Server listening at %v", listener.Addr())

	if err := s.Serve(listener); err != nil {
		log.Fatalf("failed to serve: %v", err)
	}
}
