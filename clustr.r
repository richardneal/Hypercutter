#!/usr/bin/Rscript

source( 'clustr1.r' );
#source('mycluster.r');
source('hclusttophylo.r');

# hacky way for now
args <- commandArgs(trailingOnly=TRUE);
ifile <-  args[1];
cwd <- args[2];
method <- args[3];
metric <- args[4];
output <- args[5];
title <- args[6];
p <- args[7];
type <- args[8];
labelFile <- args[9];
scrubtags <- args[10];
divitags <- args[11];


filename <- paste(cwd,runif(1), sep="" );

if(output == "phyloxml")
{
	filename <- paste(filename, ".xml", sep="");
}

rownames<-myCluster( ifile, method=method, metric=metric, output.type=output,
        outputfile=filename, main=title, p=p, type=type, labelFile=labelFile,
	scrubtags=scrubtags, divitags=divitags);

cat(filename,rownames,sep=",");

