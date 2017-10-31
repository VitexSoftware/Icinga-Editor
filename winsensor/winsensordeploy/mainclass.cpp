#include "mainclass.h"
#include "downloader.h"

#include <QDebug>
MainClass::MainClass(QObject *parent) :
    QObject(parent)
{
    // get the instance of the main application
    app = QCoreApplication::instance();
    // setup everything here
    // create any global objects
    // setup debug and warning mode
}

// 10ms after the application starts this method will run
// all QT messaging is running at this point so threads, signals and slots
// will all work as expected.
void MainClass::run()
{
    // Add your main code here
    qDebug() << "MainClass.Run is executing";
    // you must call quit when complete or the program will stay in the
    // messaging loop
    Downloader d;
    d.doDownload();


//    quit();
}

// call this routine to quit the application
void MainClass::quit()
{
    // you can do some cleanup here
    // then do emit finished to signal CoreApplication to quit
    emit finished();
}

// shortly after quit is called the CoreApplication will signal this routine
// this is a good place to delete any objects that were created in the
// constructor and/or to stop any threads
void MainClass::aboutToQuitApp()
{
    // stop threads
    // sleep(1);   // wait for threads to stop.
    // delete any objects
}

